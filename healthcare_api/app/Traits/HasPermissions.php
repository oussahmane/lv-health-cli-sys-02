<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasPermissions
{
    /**
     * User can have multiple roles (across different clinics eventually, 
     * but currently one per clinic context).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Check if user has a specific permission slug.
     */
    public function hasPermissionTo(string $permission): bool
    {
        // Super Admin bypass
        if ($this->role === 'super_admin') return true;

        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    /**
     * Check if user has a specific role slug.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Sync roles for the user.
     */
    public function assignRole(string $roleSlug, $clinicId = null)
    {
        $role = Role::where('slug', $roleSlug)
            ->where(function($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId)->orWhereNull('clinic_id');
            })->first();

        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    /**
     * Get all permission slugs for the user.
     */
    public function getPermissionSlugs(): array
    {
        if ($this->role === 'super_admin') {
            return ['*'];
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('slug');
            })
            ->unique()
            ->values()
            ->all();
    }
}
