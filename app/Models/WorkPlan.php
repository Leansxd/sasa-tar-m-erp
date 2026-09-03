<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlan extends Model
{
    use HasFactory;

    protected $table = 'is_planlari';

    protected $fillable = [
        'production_location_id',
        'job_type_id',
        'assigned_personnel_id',
        'title',
        'plan_date',
        'due_date',
        'status',
        'description',
        'completion_notes',
        'photo_path',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'due_date' => 'date',
    ];

    public function productionLocation(): BelongsTo
    {
        return $this->belongsTo(ProductionLocation::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function assignedPersonnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'assigned_personnel_id');
    }

    public function comments()
    {
        return $this->hasMany(WorkPlanComment::class)->with('personnel')->latest();
    }
}
