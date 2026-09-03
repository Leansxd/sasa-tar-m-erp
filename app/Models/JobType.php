<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobType extends Model
{
    use HasFactory;

    protected $table = 'is_tanimlari';

    protected $fillable = [
        'name',
        'code',
        'form_type',
        'description',
    ];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(UnitDefinition::class, 'is_tanimi_birim');
    }
}
