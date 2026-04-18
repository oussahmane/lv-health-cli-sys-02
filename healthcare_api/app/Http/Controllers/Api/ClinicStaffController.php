<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClinicStaffController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $staff = User::where('clinic_id', $clinicId)
            ->where(function($q) {
                $q->where('role', 'secretary')->orWhere('role', 'doctor');
            })
            ->with('roles')
            ->get();

        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:secretary,doctor',
        ]);

        return \DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'clinic_id' => $request->user()->clinic_id,
                'is_active' => true,
            ]);

            // Assign RBAC Role
            $user->assignRole($request->role);

            return response()->json($user->load('roles'), 201);
        });
    }

    public function toggleActive(User $user, Request $request)
    {
        if ($user->clinic_id !== $request->user()->clinic_id) abort(403);
        
        $user->update(['is_active' => !$user->is_active]);
        
        return response()->json($user);
    }
}
