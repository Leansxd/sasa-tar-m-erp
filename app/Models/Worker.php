<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worker extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'isciler';

    protected $fillable = [
        'tenant_id',
        'crew_leader_id',
        'first_name',
        'last_name',
        'identity_number',
        'photo_path',
        'id_photo_path',
        'performance_rating',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'performance_rating' => 'integer',
        'is_active' => 'boolean',
    ];

    public function crewLeader(): BelongsTo
    {
        return $this->belongsTo(CrewLeader::class);
    }
}
