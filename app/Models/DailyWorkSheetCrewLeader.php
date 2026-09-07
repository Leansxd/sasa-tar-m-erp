<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkSheetCrewLeader extends Model
{
    use HasFactory;

    protected $table = 'gunluk_form_cavuslar';

    protected $fillable = [
        'daily_work_sheet_id',
        'crew_leader_id',
        'worker_count',
        'car_count',
        'second_driver_fee_type',
        'overtime_hours',
        'overtime_end_time',
        'extra_wage_per_worker',
        'travel_fee',
        'meal_fee',
        'ramadan_meal_count',
        'is_food_included_override',
        'calculated_wage_total',
        'dia_cari_code',
    ];

    protected $casts = [
        'worker_count' => 'integer',
        'car_count' => 'integer',
        'overtime_hours' => 'decimal:2',
        'extra_wage_per_worker' => 'decimal:2',
        'travel_fee' => 'decimal:2',
        'meal_fee' => 'decimal:2',
        'ramadan_meal_count' => 'integer',
        'is_food_included_override' => 'boolean',
        'calculated_wage_total' => 'decimal:2',
    ];

    public function workSheet(): BelongsTo
    {
        return $this->belongsTo(DailyWorkSheet::class, 'daily_work_sheet_id');
    }

    public function crewLeader(): BelongsTo
    {
        return $this->belongsTo(CrewLeader::class);
    }
}
