<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPrice extends Model
{
    protected $fillable = [
        'crypto_id',
        'price_usd',
        'market_cap',
        'volume_24h',
        'captured_at',
    ];

    // Una entrada de precio pertenece a una crypto
    public function crypto(): BelongsTo
    {
        return $this->belongsTo(Crypto::class);
    }
}
