<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\Counter;
use App\Models\Plan;
use App\Models\Queue;
use App\Models\QueueSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinicDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $plans = Plan::all();
        
        // 1. ELITE MEDICAL CENTER (Enterprise)
        $enterprisePlan = Plan::where('name', 'Enterprise Clinic')->first();
        $elite = Clinic::create([
            'name' => 'Elite Medical Center',
            'slug' => 'elite-medical',
            'address' => '123 Healthcare Blvd, Medical District',
            'phone' => '0550111222',
            'timezone' => 'Africa/Algiers',
            'plan_id' => $enterprisePlan->id,
            'is_active' => true,
        ]);

        // Creating Rooms for Elite
        $rooms = [
            ['name' => 'Consultation Room 1', 'description' => 'General Medicine'],
            ['name' => 'Consultation Room 2', 'description' => 'Pediatrics'],
            ['name' => 'Reception Counter', 'description' => 'Main Entrance'],
        ];
        foreach ($rooms as $r) {
            Counter::create(array_merge($r, ['clinic_id' => $elite->id, 'is_active' => true]));
        }

        // Creating Staff for Elite
        $this->createStaff($elite, 'Dr. Elias Elias', 'admin@elitemedical.com', 'clinic_admin');
        $this->createStaff($elite, 'Sarah Secretary', 'staff@elitemedical.com', 'secretary');
        $this->createStaff($elite, 'Dr. James Smith', 'doctor@elitemedical.com', 'doctor');

        $this->setupClinicOperationalData($elite, $today);

        // 2. PRIME CARE CLINIC (Small)
        $smallPlan = Plan::where('name', 'Small Clinic')->first();
        $prime = Clinic::create([
            'name' => 'Prime Care Clinic',
            'slug' => 'prime-care',
            'address' => '45 Neighborhood St, East Side',
            'phone' => '0560333444',
            'timezone' => 'Africa/Algiers',
            'plan_id' => $smallPlan->id,
            'is_active' => true,
        ]);

        Counter::create(['clinic_id' => $prime->id, 'name' => 'Main Desk', 'is_active' => true]);

        $this->createStaff($prime, 'Admin Prime', 'admin@primecare.com', 'clinic_admin');
        $this->createStaff($prime, 'Staff Prime', 'staff@primecare.com', 'secretary');

        $this->setupClinicOperationalData($prime, $today);
    }

    private function createStaff($clinic, $name, $email, $roleSlug)
    {
        $role = Role::where('slug', $roleSlug)->whereNull('clinic_id')->first();
        
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $roleSlug,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
    }

    private function setupClinicOperationalData($clinic, $today)
    {
        // Working Hours
        for ($i = 0; $i < 7; $i++) {
            ClinicWorkingHour::create([
                'clinic_id' => $clinic->id,
                'weekday' => $i,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_open' => true,
            ]);
        }

        // Daily Settings
        QueueSetting::create([
            'clinic_id' => $clinic->id,
            'date' => $today,
            'max_patients' => 100,
            'is_open' => true,
            'is_paused' => false,
        ]);

        // Demo Queue
        $patients = [
            ['name' => 'Alice Johnson', 'status' => 'done'],
            ['name' => 'Bob Thompson', 'status' => 'done'],
            ['name' => 'Charlie Davis', 'status' => 'called'],
            ['name' => 'Diana Prince', 'status' => 'waiting'],
            ['name' => 'Edward Norton', 'status' => 'waiting'],
        ];

        $counter = $clinic->physicalCounters()->first();

        foreach ($patients as $index => $p) {
            $num = $index + 1;
            Queue::create([
                'clinic_id' => $clinic->id,
                'patient_name' => $p['name'],
                'patient_phone' => '0555' . str_pad($num, 6, '0', STR_PAD_LEFT),
                'queue_number' => $num,
                'date' => $today,
                'status' => $p['status'],
                'source' => 'online_link',
                'cancel_token' => Str::random(32),
                'counter_id' => $p['status'] === 'called' ? $counter->id : ($p['status'] === 'done' ? $counter->id : null),
                'called_at' => $p['status'] !== 'waiting' ? now()->subMinutes(30 - $num * 5) : null,
                'done_at' => $p['status'] === 'done' ? now()->subMinutes(10) : null,
            ]);
        }
    }
}
