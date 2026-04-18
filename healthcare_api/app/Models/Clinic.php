<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'logo',
        'timezone',
        'avg_minutes_per_patient',
        'default_max_patients',
        'is_active',
        'display_ticker',
        'plan_id',
        'payment_status',
        'activated_at',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'avg_minutes_per_patient' => 'integer',
        'default_max_patients' => 'integer',
        'activated_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'clinic_admin');
    }

    public function secretaries(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'secretary');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(ClinicWorkingHour::class);
    }

    public function queueSettings(): HasMany
    {
        return $this->hasMany(QueueSetting::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function counters(): HasMany
    {
        return $this->hasMany(QueueCounter::class);
    }

    public function physicalCounters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function screens(): HasMany
    {
        return $this->hasMany(ClinicScreen::class);
    }
}
