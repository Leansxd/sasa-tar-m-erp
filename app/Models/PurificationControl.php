<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurificationControl extends Model
{
    use HasFactory;

    protected $table = 'aritma_suyu_kontrolleri';

    protected $fillable = [
        'water_source_id',
        'control_date',
        'inlet_pressure_bar',
        'outlet_pressure_bar',
        'delta_pressure_bar',
        'max_threshold_bar',
        'has_warning',
        'warning_message',
    ];

    protected $casts = [
        'control_date' => 'date',
        'inlet_pressure_bar' => 'decimal:2',
        'outlet_pressure_bar' => 'decimal:2',
        'delta_pressure_bar' => 'decimal:2',
        'max_threshold_bar' => 'decimal:2',
        'has_warning' => 'boolean',
    ];

    public function waterSource(): BelongsTo
    {
        return $this->belongsTo(WaterSource::class);
    }
}
