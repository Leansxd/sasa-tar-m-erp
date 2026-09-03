<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkSheetHarvestItem extends Model
{
    use HasFactory;

    protected $table = 'gunluk_form_hasat_kalemleri';

    protected $fillable = [
        'daily_work_sheet_id',
        'product_id',
        'product_subtype_id',
        'packaging_definition_id',
        'package_count',
        'quantity',
        'unit_symbol',
        'unit_price',
        'total_revenue',
        'crop_type',
        'banana_bunch_count',
        'farm_scale_kg',
        'merchant_scale_1st_kg',
        'merchant_scale_2nd_kg',
        'is_merchant_weighed',
        'weighed_by_id',
    ];

    protected $casts = [
        'package_count' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'banana_bunch_count' => 'integer',
        'farm_scale_kg' => 'decimal:2',
        'merchant_scale_1st_kg' => 'decimal:2',
        'merchant_scale_2nd_kg' => 'decimal:2',
        'is_merchant_weighed' => 'boolean',
    ];

    public function workSheet(): BelongsTo
    {
        return $this->belongsTo(DailyWorkSheet::class, 'daily_work_sheet_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productSubtype(): BelongsTo
    {
        return $this->belongsTo(ProductSubtype::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(PackagingDefinition::class, 'packaging_definition_id');
    }

    public function weighedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'weighed_by_id');
    }
}
