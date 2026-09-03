<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CateringSupplier extends Model
{
    use HasFactory;

    protected $table = 'yemek_tedarikcileri';

    protected $fillable = [
        'company_title',
        'contact_person',
        'phone',
        'meal_unit_price',
        'is_vat_included',
        'dia_cari_code',
    ];

    protected $casts = [
        'meal_unit_price' => 'decimal:2',
        'is_vat_included' => 'boolean',
    ];
}
