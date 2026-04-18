<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Queue;
use App\Models\User;
use App\Models\Plan;
use App\Services\ClinicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    protected $clinicService;

    public function __construct(ClinicService $clinicService)
    {
        $this->clinicService = $clinicService;
    }

    /**
     * Get platform-wide statistics.
     */
    public function stats()
    {
        $today = now()->toDateString();
        
        return response()->json([
            'total_clinics' => Clinic::count(),
            'active_clinics_today' => Clinic::where('is_active', true)->count(),
            'total_queue_entries_today' => Queue::where('date', $today)->count(),
        ]);
    }

    /**
     * List all clinics with their primary admins and plans.
     */
    public function indexClinics()
    {
        return response()->json(Clinic::with(['plan', 'admins' => function($q) {
            $q->orderBy('created_at', 'asc');
        }])->withCount(['users' => function($q) {
            $q->where('role', 'clinic_admin');
        }])->get());
    }

    /**
     * Create a new clinic.
     */
    public function storeClinic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'timezone' => 'required|string',
            'plan_id' => 'required|exists:plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8',
        ]);

        $clinic = $this->clinicService->createClinic($request->all());

        return response()->json($clinic->load('admins'), 201);
    }

    /**
     * Update clinic details and its primary admin.
     */
    public function updateClinic(Request $request, Clinic $clinic)
    {
        $admin = $clinic->admins()->orderBy('created_at', 'asc')->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'timezone' => 'required|string',
            'plan_id' => 'required|exists:plans,id',
            'is_active' => 'required|boolean',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email,' . ($admin ? $admin->id : 'NULL'),
            'admin_password' => 'nullable|min:8',
        ]);

        $updatedClinic = $this->clinicService->updateClinic($clinic, $request->all());

        return response()->json($updatedClinic->load(['admins', 'plan']));
    }

    /**
     * Toggle clinic activation status.
     */
    public function toggleClinicStatus(Clinic $clinic)
    {
        $clinic->update(['is_active' => !$clinic->is_active]);
        return response()->json($clinic);
    }

    /**
     * Toggle user (staff) activation status.
     */
    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return response()->json($user);
    }

    /**
     * List all available plans.
     */
    public function listPlans()
    {
        return response()->json(Plan::all());
    }

    /**
     * Manage plans (CRUD delegates could be added if needed).
     */
    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_doctors' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_screens' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $plan = Plan::create($request->all());
        return response()->json($plan, 201);
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_doctors' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_screens' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $plan->update($request->all());
        return response()->json($plan);
    }

    public function deletePlan(Plan $plan)
    {
        if ($plan->clinics()->count() > 0) {
            return response()->json(['message' => 'Cannot delete plan that is still in use by clinics.'], 403);
        }

        $plan->delete();
        return response()->json(['message' => 'Plan deleted successfully.']);
    }
}
