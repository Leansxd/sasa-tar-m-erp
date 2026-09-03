<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrewLeader extends Model
{
    use HasFactory;

    protected $table = 'cavuslar';

    protected $fillable = [
        'first_name',
        'last_name',
        'identity_number',
        'phone',
        'origin_city',
        'photo_path',
        'id_photo_path',
        'daily_wage',
        'multiplier',
        'is_leader_fee_included',
        'min_car_requirement',
        'travel_fee_per_car',
        'is_food_included',
        'dia_cari_code',
    ];

    protected $casts = [
        'daily_wage' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'is_leader_fee_included' => 'boolean',
        'min_car_requirement' => 'integer',
        'travel_fee_per_car' => 'decimal:2',
        'is_food_included' => 'boolean',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
