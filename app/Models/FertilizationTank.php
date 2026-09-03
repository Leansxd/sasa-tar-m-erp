<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FertilizationTank extends Model
{
    use HasFactory;

    protected $table = 'gubre_tanklari';

    protected $fillable = [
        'fertilization_recipe_id',
        'tank_name',
        'capacity_liters',
    ];

    protected $casts = [
        'capacity_liters' => 'decimal:2',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(FertilizationRecipe::class, 'fertilization_recipe_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FertilizationTankItem::class);
    }
}
