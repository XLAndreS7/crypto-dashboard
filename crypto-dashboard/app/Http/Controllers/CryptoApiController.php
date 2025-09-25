<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Crypto;
use Illuminate\Http\Request;

class CryptoApiController extends Controller
{
    
    public function index()
    {
        return Crypto::with(['prices' => fn($q) => $q->latest('captured_at')->limit(1)])
            ->get(['id','name','symbol']);
    }

    public function show($symbol)
    {
        return Crypto::where('symbol', strtoupper($symbol))
            ->with(['prices' => fn($q) => $q->orderByDesc('captured_at')->limit(10)])
            ->firstOrFail();
    }
}
