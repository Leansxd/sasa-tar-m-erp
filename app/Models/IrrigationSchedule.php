<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IrrigationSchedule extends Model
{
    use HasFactory;

    protected $table = 'sulama_programlari';

    protected $fillable = [
        'schedule_date',
        'run_number',
        'start_time',
        'is_fertilized',
        'fertilization_recipe_id',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'run_number' => 'integer',
        'is_fertilized' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(FertilizationRecipe::class, 'fertilization_recipe_id');
    }

    public function valves(): HasMany
    {
        return $this->hasMany(IrrigationScheduleValve::class);
    }
}
