<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkSheetWorkerAssignment extends Model
{
    use HasFactory;

    protected $table = 'gunluk_form_isci_atamalari';

    protected $fillable = [
        'daily_work_sheet_id',
        'crew_leader_id',
        'worker_id',
        'personnel_id',
        'job_type_id',
        'start_time',
        'end_time',
        'break_minutes',
    ];

    protected $casts = [
        'break_minutes' => 'integer',
    ];

    public function workSheet(): BelongsTo
    {
        return $this->belongsTo(DailyWorkSheet::class, 'daily_work_sheet_id');
    }

    public function crewLeader(): BelongsTo
    {
        return $this->belongsTo(CrewLeader::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }
}
