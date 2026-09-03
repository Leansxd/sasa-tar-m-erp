<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UnitDefinition extends Model
{
    use HasFactory;

    protected $table = 'birim_tanimlari';

    protected $fillable = [
        'name',
        'symbol',
        'unit_category',
    ];

    public function jobTypes(): BelongsToMany
    {
        return $this->belongsToMany(JobType::class, 'job_type_unit');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_unit');
    }
}
