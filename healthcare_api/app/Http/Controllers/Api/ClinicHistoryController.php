<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class ClinicHistoryController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $date = $request->query('date', now()->toDateString());

        $query = Queue::where('clinic_id', $clinicId)
            ->where('date', $date);

        // Doctor Scoping: only see patients they personally consulted
        if ($request->user()->hasRole('doctor')) {
            $query->where('handled_by', $request->user()->id);
        }

        $queues = $query->get();

        return response()->json($queues);
    }

    public function export(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $date = $request->query('date', now()->toDateString());

        $query = Queue::where('clinic_id', $clinicId)
            ->where('date', $date);

        // Doctor Scoping
        if ($request->user()->hasRole('doctor')) {
            $query->where('handled_by', $request->user()->id);
        }

        $queues = $query->get();

        $filename = "queue_history_{$date}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($queues) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Number', 'Patient Name', 'Phone', 'Status', 'Source', 'Priority', 'Registered', 'Called At', 'Done At']);

            foreach ($queues as $q) {
                fputcsv($file, [
                    $q->id,
                    $q->queue_number,
                    $q->patient_name,
                    $q->patient_phone,
                    $q->status,
                    $q->source,
                    $q->priority ? 'Yes' : 'No',
                    $q->created_at,
                    $q->called_at,
                    $q->done_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
