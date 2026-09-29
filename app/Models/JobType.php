<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'is_tanimlari';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'form_type',
        'description',
    ];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(UnitDefinition::class, 'is_tanimi_birim');
    }
}
