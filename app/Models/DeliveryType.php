<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'teslimat_sekilleri';

    protected $fillable = [
        'tenant_id',
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
