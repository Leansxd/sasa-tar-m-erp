<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionLocation extends Model
{
    use HasFactory;

    protected $table = 'uretim_yerleri';

    protected $fillable = [
        'company_id',
        'name',
        'location_type',
        'dia_branch_code',
        'dia_warehouse_code',
        'total_area_dekar',
        'approx_plant_count',
    ];

    protected $casts = [
        'total_area_dekar' => 'decimal:2',
        'approx_plant_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ProductionSection::class);
    }

    public function valves(): HasMany
    {
        return $this->hasMany(LocationValve::class);
    }

    public function waterSources(): HasMany
    {
        return $this->hasMany(WaterSource::class);
    }

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }
}
