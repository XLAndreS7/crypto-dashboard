<?php

use Illuminate\Support\Facades\Route;
use App\Models\Crypto;

Route::redirect('/', '/cryptos'); // solo una “home”

Route::get('/cryptos', function () {
    $cryptos = Crypto::with(['prices' => fn($q) => $q->orderByDesc('captured_at')->limit(1)])
        ->orderBy('symbol')
        ->limit(10)
        ->get();

    return view('cryptos.index', compact('cryptos'));
});
