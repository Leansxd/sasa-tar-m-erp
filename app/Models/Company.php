<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $table = 'firmalar';

    protected $fillable = [
        'name',
        'code',
        'tax_number',
        'dia_company_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productionLocations(): HasMany
    {
        return $this->hasMany(ProductionLocation::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'firma_urun');
    }
}
