<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'urunler';

    protected $fillable = [
        'name',
        'code',
        'product_type',
        'dia_stock_code',
        'description',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'firma_urun');
    }

    public function subtypes(): HasMany
    {
        return $this->hasMany(ProductSubtype::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(UnitDefinition::class, 'urun_birim');
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(PackagingDefinition::class);
    }
}
