<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            ['name' => 'Manage Staff', 'slug' => 'manage_staff', 'group' => 'staff'],
            ['name' => 'Manage Queue', 'slug' => 'manage_queue', 'group' => 'queue'],
            ['name' => 'Consult Patients', 'slug' => 'consult_patients', 'group' => 'medical'],
            ['name' => 'View History', 'slug' => 'view_history', 'group' => 'medical'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'group' => 'settings'],
            ['name' => 'View Billing', 'slug' => 'view_billing', 'group' => 'settings'],
            ['name' => 'View Analytics', 'slug' => 'view_analytics', 'group' => 'settings'],
        ];

        foreach ($permissions as $p) {
            \App\Models\Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 2. Create Default Roles (System-wide templates)
        $owner = \App\Models\Role::updateOrCreate(
            ['slug' => 'clinic_admin', 'clinic_id' => null],
            ['name' => 'Clinic Owner', 'slug' => 'clinic_admin']
        );
        $owner->permissions()->sync(\App\Models\Permission::all());

        $secretary = \App\Models\Role::updateOrCreate(
            ['slug' => 'secretary', 'clinic_id' => null],
            ['name' => 'Secretary', 'slug' => 'secretary']
        );
        $secretary->permissions()->sync(\App\Models\Permission::whereIn('slug', ['manage_queue'])->get());

        $doctor = \App\Models\Role::updateOrCreate(
            ['slug' => 'doctor', 'clinic_id' => null],
            ['name' => 'Doctor', 'slug' => 'doctor']
        );
        $doctor->permissions()->sync(\App\Models\Permission::whereIn('slug', ['consult_patients', 'view_history'])->get());
    }
}
