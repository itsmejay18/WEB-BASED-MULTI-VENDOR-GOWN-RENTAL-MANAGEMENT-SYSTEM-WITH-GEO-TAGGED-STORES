<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMeasurement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'height_cm',
        'weight_kg',
        'chest_cm',
        'waist_cm',
        'hips_cm',
        'inseam_cm',
        'shoulder_width_cm',
        'dress_size',
        'pant_size',
        'shirt_size',
        'shoe_size',
        'last_updated',
    ];

    protected $casts = [
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'chest_cm' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'hips_cm' => 'decimal:2',
        'inseam_cm' => 'decimal:2',
        'shoulder_width_cm' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
