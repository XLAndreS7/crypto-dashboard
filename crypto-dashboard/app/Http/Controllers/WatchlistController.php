<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Crypto;
use App\Models\WatchlistItem;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * GET /api/watchlist
     * Devuelve la watchlist del usuario con último precio (si existe).
     */
    public function index()
    {
        $items    = WatchlistItem::orderBy('symbol')->get();
        $symbols  = $items->pluck('symbol')->all();

        
        $cryptos = Crypto::whereIn('symbol', $symbols)
            ->with(['prices' => fn($q) => $q->orderByDesc('captured_at')->limit(1)])
            ->get()
            ->keyBy('symbol');

        $out = [];
        foreach ($symbols as $sym) {
            $c = $cryptos->get($sym);
            $p = optional($c?->prices?->first());
            $out[] = [
                'symbol'         => $sym,
                'name'           => $c?->name ?? $sym,
                'last_price_usd' => $p?->price_usd,
                'market_cap'     => $p?->market_cap,
                'volume_24h'     => $p?->volume_24h,
                'captured_at'    => $p?->captured_at,
            ];
        }

        return response()->json($out);
    }

    /**
     * POST /api/watchlist  { "symbol": "BTC" }
     * Agrega un símbolo a la watchlist (y crea la Crypto si no existe).
     */
    public function store(Request $request)
    {
        $symbol = strtoupper((string) $request->input('symbol', ''));
        if (!$symbol) {
            return response()->json(['message' => 'El campo symbol es requerido'], 422);
        }

        
        $crypto = Crypto::firstOrCreate(
            ['symbol' => $symbol],
            ['name'   => $symbol]
        );

        
        WatchlistItem::firstOrCreate(
            ['symbol' => $symbol],
            ['crypto_id' => $crypto->id]
        );

        return response()->json([
            'ok'  => true,
            'msg' => "Símbolo {$symbol} agregado a la lista.",
        ], 201);
    }

   
    public function destroy(string $symbol)
    {
        $symbol = strtoupper($symbol);
        $item = WatchlistItem::where('symbol', $symbol)->first();

        if (!$item) {
            return response()->json(['message' => 'No existe en tu lista'], 404);
        }

        $item->delete();
        return response()->json(['ok' => true, 'msg' => "Símbolo {$symbol} eliminado."]);
    }

    /**
     * GET /api/cryptos/search?q=eth
     * Sugerencias por símbolo/nombre (base local).
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $res = Crypto::query()
            ->where('symbol', 'like', strtoupper($q) . '%')
            ->orWhere('name', 'like', '%' . $q . '%')
            ->orderBy('symbol')
            ->limit(10)
            ->get(['symbol', 'name']);

        return response()->json($res);
    }
}

