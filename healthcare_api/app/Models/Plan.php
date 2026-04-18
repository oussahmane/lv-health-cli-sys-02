<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_doctors',
        'max_staff',
        'max_screens',
        'price',
    ];

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }
}
