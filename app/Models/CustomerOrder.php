<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    use HasFactory;

    protected $table = 'musteri_siparisleri';

    protected $fillable = [
        'trading_party_id',
        'order_date',
        'requested_delivery_date',
        'contact_person',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'requested_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function tradingParty(): BelongsTo
    {
        return $this->belongsTo(TradingParty::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(ShipmentDelivery::class);
    }
}
