<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizationTankItem extends Model
{
    use HasFactory;

    protected $table = 'gubre_tank_icerikleri';

    protected $fillable = [
        'fertilization_tank_id',
        'product_id',
        'product_name',
        'brand',
        'quantity',
        'unit',
        'usage_purpose',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(FertilizationTank::class, 'fertilization_tank_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
