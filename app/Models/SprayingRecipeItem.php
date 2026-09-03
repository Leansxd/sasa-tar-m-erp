<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprayingRecipeItem extends Model
{
    use HasFactory;

    protected $table = 'ilac_recete_icerikleri';

    protected $fillable = [
        'spraying_recipe_id',
        'product_id',
        'product_name',
        'brand',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(SprayingRecipe::class, 'spraying_recipe_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
