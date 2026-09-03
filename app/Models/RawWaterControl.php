<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawWaterControl extends Model
{
    use HasFactory;

    protected $table = 'kaynak_suyu_kontrolleri';

    protected $fillable = [
        'water_source_id',
        'control_date',
        'ec_val',
        'ph_val',
        'pump_status',
        'pump_fault_note',
        'active_start_date',
        'passive_start_date',
        'source_switch_reason',
        'is_filter_cleaned',
        'filter_cleaned_photo',
        'water_tank_level',
        'water_tank_photo',
        'water_tank_note',
        'chlorine_tank_level',
        'dosing_pump_mode',
        'dosing_pump_manual_val',
        'dosing_pump_fault_note',
    ];

    protected $casts = [
        'control_date' => 'date',
        'active_start_date' => 'date',
        'passive_start_date' => 'date',
        'ec_val' => 'decimal:2',
        'ph_val' => 'decimal:2',
        'is_filter_cleaned' => 'boolean',
    ];

    public function waterSource(): BelongsTo
    {
        return $this->belongsTo(WaterSource::class);
    }
}
