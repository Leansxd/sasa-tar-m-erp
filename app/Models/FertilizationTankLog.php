<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizationTankLog extends Model
{
    use HasFactory;

    protected $table = 'gubre_tank_loglari';

    protected $fillable = [
        'fertilization_run_id',
        'fertilization_tank_id',
        'prepared_at',
        'prepared_by_id',
        'tank_name',
        'notes',
    ];

    protected $casts = [
        'prepared_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(FertilizationRun::class, 'fertilization_run_id');
    }

    public function tank(): BelongsTo
    {
        return $this->belongsTo(FertilizationTank::class, 'fertilization_tank_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'prepared_by_id');
    }
}
