<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlanLimitService;
use App\Models\Clinic;
 
class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type = null): Response
    {
        $clinic = Clinic::with('plan')->find($request->user()->clinic_id);
 
        if (!$clinic) {
            return response()->json(['message' => 'Clinic not found.'], 404);
        }

        // Super Admin bypass
        if ($request->user()->role === 'super_admin') {
             return $next($request);
        }

        if (!$clinic->plan) {
            return response()->json(['message' => 'No active plan found for this clinic. Please select a plan.'], 403);
        }
 
        $canAdd = true;
        $message = "Limit reached for this feature in your current plan ({$clinic->plan->name}).";
        
        // Auto-detect type if not provided
        if (!$type) {
            if ($request->role === 'doctor') $type = 'doctor';
            if ($request->role === 'secretary') $type = 'staff';
        }
 
        switch ($type) {
            case 'doctor':
                $canAdd = PlanLimitService::canAddDoctor($clinic);
                $message = "You have reached the maximum number of doctors allowed in your plan ({$clinic->plan->max_doctors}).";
                break;
            case 'staff':
                $canAdd = PlanLimitService::canAddStaff($clinic);
                $message = "You have reached the maximum number of staff members allowed in your plan ({$clinic->plan->max_staff}).";
                break;
            case 'screen':
                $canAdd = PlanLimitService::canAddScreen($clinic);
                $message = "You have reached the maximum number of TV screens allowed in your plan ({$clinic->plan->max_screens}).";
                break;
        }
 
        if (!$canAdd) {
            return response()->json(['message' => $message, 'limit_reached' => true], 403);
        }
 
        return $next($request);
    }
}
