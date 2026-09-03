<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    use HasFactory;

    protected $table = 'hal_piyasa_fiyatlari';

    protected $fillable = [
        'product_id',
        'price_date',
        'unit_price',
        'source_name',
    ];

    protected $casts = [
        'price_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
