<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\Queue;
use App\Models\QueueSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinicsData = [
            [
                'name' => 'City Health Center',
                'patients_count' => 3,
                'email' => 'city@clinic.com'
            ],
            [
                'name' => 'Wellness Wellness Clinic',
                'patients_count' => 5,
                'email' => 'wellness@clinic.com'
            ],
            [
                'name' => 'Prime Care Hospital',
                'patients_count' => 10,
                'email' => 'prime@clinic.com'
            ],
        ];

        $today = now()->toDateString();

        foreach ($clinicsData as $index => $data) {
            // Create Clinic
            $clinic = Clinic::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
                'address' => 'Test Address ' . ($index + 1),
                'phone' => '055500000' . ($index + 1),
                'timezone' => 'Africa/Algiers',
                'is_active' => true,
            ]);

            // Create Admin
            User::create([
                'name' => $data['name'] . ' Admin',
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'clinic_admin',
                'clinic_id' => $clinic->id,
                'is_active' => true,
            ]);

            // Create Working Hours (7 days)
            for ($i = 0; $i < 7; $i++) {
                ClinicWorkingHour::create([
                    'clinic_id' => $clinic->id,
                    'weekday' => $i,
                    'open_time' => '08:00:00',
                    'close_time' => '23:00:00',
                    'is_open' => true, 
                ]);
            }

            // Create Queue Setting for Today
            QueueSetting::create([
                'clinic_id' => $clinic->id,
                'date' => $today,
                'max_patients' => 50,
                'is_open' => true,
                'is_paused' => false,
            ]);

            // Create Patients/Queue Entries for Today
            for ($p = 1; $p <= $data['patients_count']; $p++) {
                Queue::create([
                    'clinic_id' => $clinic->id,
                    'patient_name' => 'Patient ' . $p . ' (' . $clinic->name . ')',
                    'patient_phone' => '0666000' . str_pad($p, 3, '0', STR_PAD_LEFT),
                    'queue_number' => $p,
                    'date' => $today,
                    'status' => $p == 1 ? 'called' : ($p == 2 ? 'done' : 'waiting'),
                    'source' => 'online_link',
                    'cancel_token' => Str::random(32),
                    'called_at' => $p == 1 ? now() : null,
                    'done_at' => $p == 2 ? now() : null,
                ]);
            }
        }
    }
}
