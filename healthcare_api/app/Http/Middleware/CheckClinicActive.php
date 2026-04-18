<?php
 
namespace App\Http\Middleware;
 
use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 
use App\Services\PlanLimitService;
 
class CheckClinicActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = null;
 
        // 1. Check by slug (Public routes)
        if ($request->route('slug')) {
            $clinic = Clinic::where('slug', $request->route('slug'))->first();
        } 
        // 2. Check by authenticated user (Clinic Admin/Staff)
        elseif ($request->user() && $request->user()->clinic_id) {
            // Bypass for Super Admin
            if ($request->user()->role === 'super_admin') {
                return $next($request);
            }
            $clinic = Clinic::find($request->user()->clinic_id);
        }
 
        // Simple active check
        if ($clinic && !$clinic->is_active) {
            return response()->json([
                'message' => 'This clinic is currently inactive. Please contact support.',
                'status' => 'inactive',
            ], 403);
        }
 
        return $next($request);
    }
}
