<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personeller';

    protected $fillable = [
        'user_id',
        'parent_personnel_id',
        'first_name',
        'last_name',
        'phone',
        'role_title',
        'permissions',
        'company_ids',
        'can_enter_backdated_data',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'company_ids' => 'array',
        'can_enter_backdated_data' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'parent_personnel_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Personnel::class, 'parent_personnel_id');
    }
}
