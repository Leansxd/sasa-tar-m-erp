<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'firmalar';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'tax_number',
        'dia_company_code',
        'owner_user_id',
        'subscription_plan',
        'max_locations',
        'max_users',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_locations' => 'integer',
        'max_users' => 'integer',
    ];

    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function productionLocations(): HasMany
    {
        return $this->hasMany(ProductionLocation::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'firma_urun');
    }
}
