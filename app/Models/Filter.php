<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Filter extends Model
{
    use HasFactory;

    protected $table = 'filtreler';

    protected $fillable = [
        'production_location_id',
        'name',
        'cleaning_cycle_days',
        'requires_photo_verification',
        'is_active',
    ];

    protected $casts = [
        'cleaning_cycle_days' => 'integer',
        'requires_photo_verification' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function waterSources(): BelongsToMany
    {
        return $this->belongsToMany(WaterSource::class, 'filtre_su_kaynagi');
    }
}
