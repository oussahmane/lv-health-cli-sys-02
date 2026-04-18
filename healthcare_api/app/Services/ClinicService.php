<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicService
{
    /**
     * Create a new clinic with its administrative account and default settings.
     */
    public function createClinic(array $data): Clinic
    {
        return DB::transaction(function () use ($data) {
            $clinic = Clinic::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
                'address' => $data['address'],
                'phone' => $data['phone'],
                'timezone' => $data['timezone'] ?? 'Africa/Algiers',
                'plan_id' => $data['plan_id'] ?? null,
                'is_active' => true,
            ]);

            $this->seedDefaultWorkingHours($clinic);
            $this->createAdminUser($clinic, $data);

            return $clinic;
        });
    }

    /**
     * Update an existing clinic and its primary admin.
     */
    public function updateClinic(Clinic $clinic, array $data): Clinic
    {
        return DB::transaction(function () use ($clinic, $data) {
            $clinic->update($data);

            $admin = $clinic->admins()->orderBy('created_at', 'asc')->first();
            if ($admin && isset($data['admin_email'])) {
                $adminData = [
                    'name' => $data['admin_name'] ?? $admin->name,
                    'email' => $data['admin_email'],
                ];
                
                if (!empty($data['admin_password'])) {
                    $adminData['password'] = Hash::make($data['admin_password']);
                }
                
                $admin->update($adminData);
            }

            return $clinic;
        });
    }

    /**
     * Seed 7 default working hours for the clinic.
     */
    protected function seedDefaultWorkingHours(Clinic $clinic): void
    {
        for ($i = 0; $i < 7; $i++) {
            ClinicWorkingHour::create([
                'clinic_id' => $clinic->id,
                'weekday' => $i,
                'open_time' => '08:00:00',
                'close_time' => '17:00:00',
                'is_open' => $i !== 5 && $i !== 6, // Managed correctly for most regions
            ]);
        }
    }

    /**
     * Create the initial admin user for the clinic.
     */
    protected function createAdminUser(Clinic $clinic, array $data): User
    {
        $user = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => 'clinic_admin',
            'clinic_id' => $clinic->id,
        ]);

        // Sync role for RBAC
        $user->assignRole('clinic_admin');

        return $user;
    }
}
