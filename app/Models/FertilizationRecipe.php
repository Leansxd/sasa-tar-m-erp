<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FertilizationRecipe extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'gubre_receteleri';

    protected $fillable = [
        'tenant_id',
        'name',
        'creation_date',
        'duration_condition',
        'creator_name',
        'water_ph',
        'water_ec',
        'water_notes',
        'is_active',
    ];

    protected $casts = [
        'creation_date' => 'date',
        'water_ph' => 'decimal:2',
        'water_ec' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tanks(): HasMany
    {
        return $this->hasMany(FertilizationTank::class);
    }
}
