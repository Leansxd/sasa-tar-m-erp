<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterAnalysisLog extends Model
{
    use HasFactory;

    protected $table = 'su_analiz_loglari';

    protected $fillable = [
        'water_source_id',
        'analysis_date',
        'ph_level',
        'ec_level',
        'chemical_details',
        'notes',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'ph_level' => 'decimal:2',
        'ec_level' => 'decimal:2',
        'chemical_details' => 'array',
    ];

    public function waterSource(): BelongsTo
    {
        return $this->belongsTo(WaterSource::class);
    }
}
