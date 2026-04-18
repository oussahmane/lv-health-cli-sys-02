<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\QueueSetting;
use App\Models\Clinic;
use App\Services\QueueService;
use Illuminate\Http\Request;

class ClinicDashboardController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Get the live dashboard state.
     */
    public function index(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $today = now()->toDateString();

        $queue = $this->queueService->getTodayQueue($clinicId);

        $counter = QueueCounter::firstOrCreate(
            ['clinic_id' => $clinicId, 'date' => $today],
            ['current_serving_number' => 0]
        );

        $settings = QueueSetting::where('clinic_id', $clinicId)
            ->where('date', $today)
            ->first();

        $physicalCounters = \App\Models\Counter::where('clinic_id', $clinicId)->where('is_active', true)->get();

        return response()->json([
            'queue' => $queue,
            'current_serving' => $counter->current_serving_number,
            'is_paused' => $settings?->is_paused ?? false,
            'pause_message' => $settings?->pause_message,
            'counters' => $physicalCounters,
        ]);
    }

    /**
     * Call the next patient.
     */
    public function callNext(Request $request)
    {
        $request->validate(['counter_id' => 'nullable|exists:counters,id']);
        $clinic = $request->user()->clinic;
        
        $next = $this->queueService->callNext($clinic, $request->user(), $request->counter_id);

        if (!$next) {
            return response()->json(['message' => 'No waiting patients found.'], 404);
        }

        return response()->json([
            'called' => $next,
            'current_serving' => $next->queue_number
        ]);
    }

    /**
     * Update the status of a specific entry.
     */
    public function updateStatus(Request $request, Queue $queue)
    {
        $request->validate(['status' => 'required|in:in_examination,done,no_show,cancelled']);
        
        if ($queue->clinic_id !== $request->user()->clinic_id) abort(403);

        $updated = $this->queueService->updateStatus($queue, $request->user(), $request->status);

        return response()->json($updated);
    }

    /**
     * Manually add a patient to the queue.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|min:3',
            'patient_phone' => 'nullable|string',
            'priority' => 'boolean',
            'priority_type' => 'string|in:regular,urgent,elderly',
        ]);

        $clinic = $request->user()->clinic;
        $today = now($clinic->timezone)->toDateString();

        // Capacity Check
        $settings = $clinic->queueSettings()->where('date', $today)->first();
        $maxPatients = $settings?->max_patients ?? $clinic->default_max_patients;
        $currentCount = $clinic->queues()->where('date', $today)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        if ($currentCount >= $maxPatients) {
            return response()->json([
                'message' => "Today's queue is full ({$maxPatients} patients max).",
            ], 403);
        }

        $queue = $this->queueService->addPatient($clinic, $request->user(), $request->all());

        return response()->json($queue, 201);
    }

    /**
     * Update priority for an entry.
     */
    public function updatePriority(Request $request, Queue $queue)
    {
        $request->validate([
            'priority' => 'required|boolean',
            'priority_type' => 'required|string|in:regular,urgent,elderly',
        ]);

        if ($queue->clinic_id !== $request->user()->clinic_id) abort(403);

        $queue->update([
            'priority' => $request->priority,
            'priority_type' => $request->priority_type,
        ]);

        event(new \App\Events\QueuePrioritySet($request->user()->clinic_id, $queue->id, $queue->priority, $queue->priority_type));

        return response()->json($queue);
    }

    /**
     * Pause or resume the queue.
     */
    public function togglePause(Request $request)
    {
        $request->validate(['is_paused' => 'required|boolean', 'message' => 'nullable|string']);
        $clinicId = $request->user()->clinic_id;
        $today = now()->toDateString();

        $settings = QueueSetting::updateOrCreate(
            ['clinic_id' => $clinicId, 'date' => $today],
            ['is_paused' => $request->is_paused, 'pause_message' => $request->message]
        );

        event(new \App\Events\QueuePauseToggled($clinicId, $request->is_paused, $request->message));

        return response()->json($settings);
    }
}
