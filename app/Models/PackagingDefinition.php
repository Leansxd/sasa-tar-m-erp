<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingDefinition extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'paketleme_tanimlari';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'code',
        'dia_stock_code',
        'capacity_qty',
        'unit_id',
    ];

    protected $casts = [
        'capacity_qty' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitDefinition::class, 'unit_id');
    }
}
