<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FertilizationRun extends Model
{
    use HasFactory;

    protected $table = 'gubre_uygulamalari';

    protected $fillable = [
        'fertilization_recipe_id',
        'start_date',
        'end_condition',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(FertilizationRecipe::class, 'fertilization_recipe_id');
    }

    public function tankLogs(): HasMany
    {
        return $this->hasMany(FertilizationTankLog::class);
    }
}
