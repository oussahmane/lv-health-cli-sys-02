<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_name',
        'patient_phone',
        'queue_number',
        'date',
        'status',
        'source',
        'priority',
        'priority_type',
        'cancel_token',
        'handled_by',
        'counter_id',
        'called_at',
        'examination_start_at',
        'done_at',
    ];

    protected $casts = [
        'date' => 'date',
        'queue_number' => 'integer',
        'priority' => 'boolean',
        'called_at' => 'datetime',
        'examination_start_at' => 'datetime',
        'done_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
