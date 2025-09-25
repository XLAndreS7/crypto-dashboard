<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crypto extends Model
{
    protected $fillable = ['name', 'symbol'];

    // Una crypto tiene muchos registros de precio
    public function prices(): HasMany
    {
        return $this->hasMany(CryptoPrice::class);
    }
}
