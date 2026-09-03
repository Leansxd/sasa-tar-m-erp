<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WaterSource extends Model
{
    use HasFactory;

    protected $table = 'su_kaynaklari';

    protected $fillable = [
        'production_location_id',
        'name',
        'active_cycle_minutes',
        'passive_cycle_minutes',
        'requires_photo_verification',
        'is_active',
    ];

    protected $casts = [
        'active_cycle_minutes' => 'integer',
        'passive_cycle_minutes' => 'integer',
        'requires_photo_verification' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'filtre_su_kaynagi');
    }
}
