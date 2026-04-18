<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $today = now()->toDateString();
        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        $todayStats = [
            'total' => Queue::where('clinic_id', $clinicId)->where('date', $today)->count(),
            'done' => Queue::where('clinic_id', $clinicId)->where('date', $today)->where('status', 'done')->count(),
            'no_show' => Queue::where('clinic_id', $clinicId)->where('date', $today)->where('status', 'no_show')->count(),
            'cancelled' => Queue::where('clinic_id', $clinicId)->where('date', $today)->where('status', 'cancelled')->count(),
        ];

        // Daily trend (last 30 days)
        $trend = Queue::where('clinic_id', $clinicId)
            ->where('date', '>=', $thirtyDaysAgo)
            ->select('date', DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Hourly distribution (peak hours)
        $peakHours = Queue::where('clinic_id', $clinicId)
            ->where('date', '>=', $thirtyDaysAgo)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Avg wait time
        $avgWait = Queue::where('clinic_id', $clinicId)
            ->where('date', '>=', $thirtyDaysAgo)
            ->whereNotNull('called_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait'))
            ->first();

        return response()->json([
            'today' => $todayStats,
            'trend' => $trend,
            'peak_hours' => $peakHours,
            'avg_wait_time' => (int) ($avgWait?->avg_wait ?? 0),
        ]);
    }
}
