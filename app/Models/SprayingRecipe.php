<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SprayingRecipe extends Model
{
    use HasFactory;

    protected $table = 'ilac_receteleri';

    protected $fillable = [
        'name',
        'creation_date',
        'usage_time',
        'usage_purpose',
        'water_volume_liters',
        'application_method',
        'is_active',
    ];

    protected $casts = [
        'creation_date' => 'date',
        'water_volume_liters' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SprayingRecipeItem::class);
    }
}
