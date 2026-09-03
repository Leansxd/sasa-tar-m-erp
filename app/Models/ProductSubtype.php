<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSubtype extends Model
{
    use HasFactory;

    protected $table = 'urun_alt_tipleri';

    protected $fillable = [
        'product_id',
        'name',
        'code',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
