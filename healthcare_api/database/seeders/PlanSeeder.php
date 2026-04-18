<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Small Clinic',
                'max_doctors' => 2,
                'max_staff' => 2,
                'max_screens' => 1,
                'price' => 0.00,
            ],
            [
                'name' => 'Medium Clinic',
                'max_doctors' => 5,
                'max_staff' => 5,
                'max_screens' => 3,
                'price' => 0.00,
            ],
            [
                'name' => 'Enterprise Clinic',
                'max_doctors' => 999, // Effectively unlimited
                'max_staff' => 999,
                'max_screens' => 999,
                'price' => 0.00,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
