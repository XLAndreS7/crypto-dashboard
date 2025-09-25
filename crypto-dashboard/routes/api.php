<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WatchlistController;

Route::get('/watchlist',                  [WatchlistController::class, 'index']);
Route::post('/watchlist',                 [WatchlistController::class, 'store']);
Route::delete('/watchlist/{symbol}',      [WatchlistController::class, 'destroy']);

Route::get('/cryptos/search',             [WatchlistController::class, 'search']);



//Route::get('/cryptos',          [CryptoApiController::class, 'index'])->name('cryptos.index');
