<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyWorkSheet extends Model
{
    use HasFactory;

    protected $table = 'gunluk_isci_formlari';

    protected $fillable = [
        'work_date',
        'company_id',
        'production_location_id',
        'has_external_workers',
        'has_internal_workers',
        'storage_destination',
        'status',
        'submitted_by_personnel_id',
        'approved_by_user_id',
        'approved_at',
        'rejection_note',
        'created_by_id',
        'updated_by_id',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'has_external_workers' => 'boolean',
        'has_internal_workers' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function crewLeaders(): HasMany
    {
        return $this->hasMany(DailyWorkSheetCrewLeader::class);
    }

    public function workerAssignments(): HasMany
    {
        return $this->hasMany(DailyWorkSheetWorkerAssignment::class);
    }

    public function harvestItems(): HasMany
    {
        return $this->hasMany(DailyWorkSheetHarvestItem::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'submitted_by_personnel_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
