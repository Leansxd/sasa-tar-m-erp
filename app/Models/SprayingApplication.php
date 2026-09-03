<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprayingApplication extends Model
{
    use HasFactory;

    protected $table = 'ilac_uygulamalari';

    protected $fillable = [
        'application_date',
        'spraying_recipe_id',
        'purpose',
        'applied_by_id',
        'covered_area_description',
        'is_tank_finished',
        'batch_code',
        'notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'is_tank_finished' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(SprayingRecipe::class, 'spraying_recipe_id');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'applied_by_id');
    }
}
