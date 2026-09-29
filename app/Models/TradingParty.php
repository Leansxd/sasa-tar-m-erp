<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingParty extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cari_taraflar';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'dia_cari_code',
        'tax_number',
        'phone',
        'email',
        'address',
        'default_transport_fee',
    ];

    protected $casts = [
        'default_transport_fee' => 'decimal:2',
    ];
}
