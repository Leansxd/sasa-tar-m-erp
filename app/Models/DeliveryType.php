<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    use HasFactory;

    protected $table = 'teslimat_sekilleri';

    protected $fillable = [
        'name',
        'code',
        'transport_by',
        'is_fee_included',
        'extra_fee',
    ];

    protected $casts = [
        'is_fee_included' => 'boolean',
        'extra_fee' => 'decimal:2',
    ];
}
