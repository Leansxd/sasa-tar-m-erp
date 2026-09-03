<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionSection extends Model
{
    use HasFactory;

    protected $table = 'uretim_bolumleri';

    protected $fillable = [
        'production_location_id',
        'name',
        'section_type',
        'area_dekar',
        'tunnel_count',
        'table_stand_count',
        'approx_plant_count',
    ];

    protected $casts = [
        'area_dekar' => 'decimal:2',
        'tunnel_count' => 'integer',
        'table_stand_count' => 'integer',
        'approx_plant_count' => 'integer',
    ];

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function valves(): HasMany
    {
        return $this->hasMany(LocationValve::class);
    }
}
