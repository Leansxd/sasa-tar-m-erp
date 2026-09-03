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
        'overtime_hours',
        'extra_wage_per_worker',
        'travel_fee',
        'meal_fee',
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
