<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['clinic', 'roles.permissions'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        // Ensure role is synchronized with RBAC tables
        if ($user->role && !$user->roles()->exists()) {
            $user->assignRole($user->role);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $permissions = $user->getPermissionSlugs();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => array_merge($user->toArray(), ['permissions' => $permissions]),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['clinic', 'roles.permissions']);

        // Ensure role is synchronized with RBAC tables on fetch
        if ($user->role && !$user->roles()->exists()) {
            $user->assignRole($user->role);
            $user->load('roles.permissions'); // Reload to get the new role link
        }

        $permissions = $user->getPermissionSlugs();

        return response()->json(array_merge($user->toArray(), ['permissions' => $permissions]));
    }
}
