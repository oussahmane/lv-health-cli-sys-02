<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\QueueSetting;
use App\Models\ClinicWorkingHour;
use App\Events\QueuePatientJoined;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PublicQueueController extends Controller
{
    public function showClinic($slug)
    {
        $clinic = Clinic::where('slug', $slug)->firstOrFail();
 
        // Check working hours
        $now = Carbon::now($clinic->timezone);
        $weekday = $now->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $workingHour = $clinic->workingHours()->where('weekday', $weekday)->first();

        $bypass = env('BYPASS_WORKING_HOURS', false);
        $isOpen24h = $workingHour && $workingHour->is_open && (is_null($workingHour->open_time) || is_null($workingHour->close_time));

        if (!$bypass && !$isOpen24h) {
            if (!$workingHour || !$workingHour->is_open || $now->format('H:i:s') < $workingHour->open_time || $now->format('H:i:s') > $workingHour->close_time) {
                return response()->json([
                    'clinic' => $clinic,
                    'message' => 'Clinic is closed.',
                    'opens_at' => $workingHour ? $workingHour->open_time : null,
                    'status' => 'closed'
                ], 403);
            }
        }

        // Check queue settings
        $settings = $clinic->queueSettings()->where('date', $now->toDateString())->first();
        if ($settings && $settings->is_paused) {
            return response()->json([
                'clinic' => $clinic,
                'message' => $settings->pause_message ?? 'Queue is currently paused.',
                'status' => 'paused'
            ], 403);
        }

        // Check capacity
        $maxPatients = $settings?->max_patients ?? $clinic->default_max_patients;
        $currentCount = $clinic->queues()->where('date', $now->toDateString())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        if ($currentCount >= $maxPatients) {
            return response()->json([
                'clinic' => $clinic,
                'message' => "Today's queue is full.",
                'next_available' => $this->findNextAvailableDate($clinic),
                'status' => 'full'
            ], 403);
        }

        // Get active servings across all counters
        $activeServings = $clinic->queues()
            ->where('date', $now->toDateString())
            ->whereIn('status', ['called', 'in_examination'])
            ->with('counter')
            ->orderBy('called_at', 'desc')
            ->get()
            ->map(function($q) {
                return [
                    'queue_number' => $q->queue_number,
                    'counter_name' => $q->counter?->name ?? 'Examination Room',
                    'patient_name' => $q->patient_name,
                ];
            });

        return response()->json([
            'clinic' => $clinic,
            'current_queue_count' => $currentCount,
            'max_patients' => $maxPatients,
            'active_servings' => $activeServings
        ]);
    }

    public function register(Request $request, $slug)
    {
        $clinic = Clinic::where('slug', $slug)->firstOrFail();
        
        $request->validate([
            'patient_name' => 'required|string|min:3',
            'patient_phone' => 'required|string', // Identity mandated
        ]);

        $today = Carbon::now($clinic->timezone)->toDateString();
        $nowTime = Carbon::now($clinic->timezone);
        $weekday = $nowTime->dayOfWeek;
        $workingHour = $clinic->workingHours()->where('weekday', $weekday)->first();

        $bypass = env('BYPASS_WORKING_HOURS', false);
        $isOpen24h = $workingHour && $workingHour->is_open && (is_null($workingHour->open_time) || is_null($workingHour->close_time));

        if (!$bypass && !$isOpen24h) {
            if (!$workingHour || !$workingHour->is_open || $nowTime->format('H:i:s') < $workingHour->open_time || $nowTime->format('H:i:s') > $workingHour->close_time) {
                return response()->json([
                    'clinic' => $clinic,
                    'message' => 'Clinic is closed.',
                    'opens_at' => $workingHour ? $workingHour->open_time : null,
                    'status' => 'closed'
                ], 403);
            }
        }

        // Check capacity
        $settings = $clinic->queueSettings()->where('date', $today)->first();
        $maxPatients = $settings?->max_patients ?? $clinic->default_max_patients;
        $currentCount = $clinic->queues()->where('date', $today)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        if ($currentCount >= $maxPatients) {
            return response()->json([
                'clinic' => $clinic,
                'message' => "Today's queue is full.",
                'status' => 'full'
            ], 403);
        }

        // Check for EXACT duplicate (same name AND phone) to prevent double-clicks
        $existing = Queue::where('clinic_id', $clinic->id)
            ->where('date', $today)
            ->where('patient_name', $request->patient_name)
            ->where('patient_phone', $request->patient_phone)
            ->whereIn('status', ['waiting', 'called', 'in_examination'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'This patient is already in the queue.',
                'ticket_url' => "/q/{$slug}/ticket/{$existing->id}?token={$existing->cancel_token}",
                'existing' => true
            ]);
        }

        // Race condition safe number assignment
        return \DB::transaction(function () use ($clinic, $request, $today) {
            $nextNumber = (Queue::where('clinic_id', $clinic->id)->where('date', $today)->max('queue_number') ?? 0) + 1;
            
            $source = $request->query('src') === 'qr' ? 'qr_scan' : 'online_link';

            $queue = Queue::create([
                'clinic_id' => $clinic->id,
                'patient_name' => $request->patient_name,
                'patient_phone' => $request->patient_phone,
                'queue_number' => $nextNumber,
                'date' => $today,
                'status' => 'waiting',
                'source' => $source,
                'cancel_token' => (string) Str::uuid(),
            ]);

            event(new QueuePatientJoined($clinic->id, $queue));

            return response()->json([
                'message' => 'Registered successfully.',
                'queue' => $queue,
                'ticket_url' => "/q/{$clinic->slug}/ticket/{$queue->id}?token={$queue->cancel_token}"
            ], 201);
        });
    }

    public function findTickets(Request $request, $slug)
    {
        $clinic = Clinic::where('slug', $slug)->firstOrFail();
        $phone = $request->query('phone');

        if (!$phone) {
            return response()->json(['message' => 'Phone number is required.'], 400);
        }

        $today = Carbon::now($clinic->timezone)->toDateString();

        $tickets = Queue::where('clinic_id', $clinic->id)
            ->where('date', $today)
            ->where('patient_phone', $phone)
            ->whereIn('status', ['waiting', 'called', 'in_examination'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'clinic' => $clinic,
            'tickets' => $tickets->map(function($t) use ($slug) {
                return [
                    'id' => $t->id,
                    'patient_name' => $t->patient_name,
                    'queue_number' => $t->queue_number,
                    'status' => $t->status,
                    'url' => "/q/{$slug}/ticket/{$t->id}?token={$t->cancel_token}"
                ];
            })
        ]);
    }

    public function showTicket($slug, $id)
    {
        $clinic = Clinic::where('slug', $slug)->firstOrFail();
        $queue = Queue::where('clinic_id', $clinic->id)->with('counter')->findOrFail($id);
        
        if (request('token') !== $queue->cancel_token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $counter = QueueCounter::where('clinic_id', $clinic->id)->where('date', $queue->date)->first();
        $servingNumber = $counter?->current_serving_number ?? 0;
        
        $ahead = max(0, $queue->queue_number - $servingNumber - 1);
        $estimatedWait = $ahead * $clinic->avg_minutes_per_patient;

        $isPaused = $clinic->queueSettings()->where('date', $queue->date)->where('is_paused', true)->exists();

        return response()->json([
            'clinic_name' => $clinic->name,
            'queue' => [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'status' => $queue->status,
                'counter_name' => $queue->counter?->name
            ],
            'serving_now' => $servingNumber,
            'people_ahead' => $ahead,
            'estimated_wait_minutes' => $estimatedWait,
            'is_paused' => $isPaused,
        ]);
    }

    public function cancelTicket($id)
    {
        $queue = Queue::findOrFail($id);
        
        if (request('token') !== $queue->cancel_token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($queue->status !== 'waiting') {
            return response()->json(['message' => 'Cannot cancel a ticket that is already being served or done.'], 403);
        }

        $queue->update(['status' => 'cancelled']);
        
        event(new \App\Events\QueueStatusUpdated($queue->clinic_id, $queue->id, 'cancelled'));

        return response()->json(['message' => 'Ticket cancelled successfully.']);
    }

    public function liveQueue($slug)
    {
        $clinic = Clinic::where('slug', $slug)->firstOrFail();
        $today = Carbon::now($clinic->timezone)->toDateString();

        $activeServings = Queue::where('clinic_id', $clinic->id)
            ->where('date', $today)
            ->whereIn('status', ['called', 'in_examination'])
            ->with('counter')
            ->orderBy('called_at', 'desc')
            ->get()
            ->map(function($q) {
                return [
                    'queue_number' => $q->queue_number,
                    'patient_name' => $q->patient_name,
                    'counter_name' => $q->counter?->name ?? 'General'
                ];
            });

        $waitingList = Queue::where('clinic_id', $clinic->id)
            ->where('date', $today)
            ->where('status', 'waiting')
            ->orderByRaw("CASE WHEN priority_type = 'urgent' THEN 1 WHEN priority_type = 'elderly' THEN 2 ELSE 3 END")
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'active_servings' => $activeServings,
            'waiting_list' => $waitingList
        ]);
    }

    private function findNextAvailableDate($clinic)
    {
        $date = Carbon::now($clinic->timezone)->addDay();
        for ($i = 0; $i < 30; $i++) {
            $workingHour = $clinic->workingHours()->where('weekday', $date->dayOfWeek)->first();
            if ($workingHour && $workingHour->is_open) {
                $settings = $clinic->queueSettings()->where('date', $date->toDateString())->first();
                if (!$settings || ($settings->is_open && !$settings->is_paused)) {
                    $max = $settings?->max_patients ?? $clinic->default_max_patients;
                    $count = $clinic->queues()->where('date', $date->toDateString())
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->count();
                    if ($count < $max) {
                        return $date->toDateString();
                    }
                }
            }
            $date->addDay();
        }
        return null;
    }
}
