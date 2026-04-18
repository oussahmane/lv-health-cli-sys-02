<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'date',
        'max_patients',
        'is_open',
        'is_paused',
        'pause_message',
    ];

    protected $casts = [
        'date' => 'date',
        'max_patients' => 'integer',
        'is_open' => 'boolean',
        'is_paused' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
