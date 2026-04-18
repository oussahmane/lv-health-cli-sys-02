<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\ActivityLog;
use App\Events\QueueNextCalled;
use App\Events\QueueStatusUpdated;
use App\Events\QueuePatientJoined;
use App\Events\QueuePrioritySet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueService
{
    /**
     * Get the current live queue for a clinic today.
     */
    public function getTodayQueue($clinicId)
    {
        $today = now()->toDateString();
        
        return Queue::where('clinic_id', $clinicId)
            ->where('date', $today)
            ->orderByRaw("CASE WHEN priority_type = 'urgent' THEN 1 WHEN priority_type = 'elderly' THEN 2 ELSE 3 END")
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->get();
    }

    /**
     * Call the next patient in the queue.
     */
    public function callNext($clinic, $user, $counterId = null)
    {
        $today = now()->toDateString();

        return DB::transaction(function () use ($clinic, $today, $user, $counterId) {
            $next = Queue::where('clinic_id', $clinic->id)
                ->where('date', $today)
                ->where('status', 'waiting')
                ->orderByRaw("CASE WHEN priority_type = 'urgent' THEN 1 WHEN priority_type = 'elderly' THEN 2 ELSE 3 END")
                ->orderBy('priority', 'desc')
                ->orderBy('queue_number', 'asc')
                ->lockForUpdate() // Crucial to prevent race conditions in multi-doctor environment
                ->first();

            if (!$next) {
                return null;
            }

            $next->update([
                'status' => 'called',
                'called_at' => now(),
                'handled_by' => $user->id,
                'counter_id' => $counterId,
            ]);

            // Update daily counter (Legacy support for single TV display if needed)
            $queueCounter = QueueCounter::where('clinic_id', $clinic->id)->where('date', $today)->first();
            if ($queueCounter) {
                $queueCounter->update(['current_serving_number' => $next->queue_number]);
            }

            $this->logActivity($clinic->id, $user->id, 'queue.called', $next->id);

            // Get counter Name
            $counterName = null;
            if ($counterId) {
                $counterName = \App\Models\Counter::where('id', $counterId)->value('name');
            }

            event(new QueueNextCalled(
                $clinic->id, 
                $next->queue_number, 
                $next->id, 
                $next->patient_name,
                $counterName
            ));

            return $next;
        });
    }

    /**
     * Add a patient to the queue manually.
     */
    public function addPatient($clinic, $user, array $data)
    {
        $today = now($clinic->timezone)->toDateString();

        return DB::transaction(function () use ($clinic, $user, $data, $today) {
            $nextNumber = (Queue::where('clinic_id', $clinic->id)->where('date', $today)->max('queue_number') ?? 0) + 1;
            
            $queue = Queue::create([
                'clinic_id' => $clinic->id,
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'] ?? null,
                'queue_number' => $nextNumber,
                'date' => $today,
                'status' => 'waiting',
                'source' => 'manual',
                'priority' => $data['priority'] ?? false,
                'priority_type' => $data['priority_type'] ?? 'regular',
                'cancel_token' => (string) Str::uuid(),
            ]);

            $this->logActivity($clinic->id, $user->id, 'queue.manual_add', $queue->id);

            event(new QueuePatientJoined($clinic->id, $queue));

            return $queue;
        });
    }

    /**
     * Update the status of a queue entry.
     */
    public function updateStatus($queue, $user, string $status)
    {
        $updateData = ['status' => $status];
        
        if ($status === 'in_examination') {
            $updateData['examination_start_at'] = now();
            if (!$queue->handled_by) $updateData['handled_by'] = $user->id;
        }
        
        if ($status === 'done') {
            $updateData['done_at'] = now();
        }

        $queue->update($updateData);

        $this->logActivity($queue->clinic_id, $user->id, "queue.{$status}", $queue->id);

        event(new QueueStatusUpdated($queue->clinic_id, $queue->id, $status));

        return $queue;
    }

    /**
     * Log activity.
     */
    protected function logActivity($clinicId, $userId, $action, $queueId)
    {
        ActivityLog::create([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'action' => $action,
            'queue_id' => $queueId,
        ]);
    }
}
