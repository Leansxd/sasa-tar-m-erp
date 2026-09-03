<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentDelivery extends Model
{
    use HasFactory;

    protected $table = 'sevkiyatlar';

    protected $fillable = [
        'customer_order_id',
        'trading_party_id',
        'product_id',
        'packaging_definition_id',
        'shipment_date',
        'quantity',
        'unit_price',
        'delivery_type_id',
        'vehicle_plate',
        'driver_name',
        'driver_phone',
        'dia_waybill_code',
        'status',
        'notes',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function tradingParty(): BelongsTo
    {
        return $this->belongsTo(TradingParty::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(PackagingDefinition::class, 'packaging_definition_id');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }
}
