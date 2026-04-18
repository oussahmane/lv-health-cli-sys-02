<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;

class PlanLimitService
{
    /**
     * Master activation check for a clinic.
     * ACTIVE = is_active AND (paid & not expired OR trial not expired)
     */
    public static function isSubscriptionValid(Clinic $clinic): bool
    {
        // Currently bypassing date checks for testing/initial phase
        return (bool) $clinic->is_active;
    }

    public static function canAddDoctor(Clinic $clinic): bool
    {
        if (!$clinic->plan) return false;
        
        $currentDoctors = User::where('clinic_id', $clinic->id)
            ->where('role', 'doctor')
            ->count();
            
        return $currentDoctors < $clinic->plan->max_doctors;
    }

    public static function canAddStaff(Clinic $clinic): bool
    {
        if (!$clinic->plan) return false;
        
        $currentStaff = User::where('clinic_id', $clinic->id)
            ->where('role', 'secretary')
            ->count();
            
        return $currentStaff < $clinic->plan->max_staff;
    }

    public static function canAddScreen(Clinic $clinic): bool
    {
        if (!$clinic->plan) return false;
        
        $currentScreens = $clinic->screens()->count();
            
        return $currentScreens < $clinic->plan->max_screens;
    }
}
