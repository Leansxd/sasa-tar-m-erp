<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationScheduleValve extends Model
{
    use HasFactory;

    protected $table = 'sulama_program_vanalari';

    protected $fillable = [
        'irrigation_schedule_id',
        'production_location_id',
        'location_valve_id',
        'duration_minutes',
        'tank_step_level',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(IrrigationSchedule::class, 'irrigation_schedule_id');
    }

    public function valve(): BelongsTo
    {
        return $this->belongsTo(LocationValve::class, 'location_valve_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class, 'production_location_id');
    }
}
