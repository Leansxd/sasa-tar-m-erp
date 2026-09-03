<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationValve extends Model
{
    use HasFactory;

    protected $table = 'uretim_vanalari';

    protected $fillable = [
        'production_location_id',
        'production_section_id',
        'valve_number',
        'name',
        'duty',
        'description',
    ];

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductionSection::class, 'production_section_id');
    }
}
