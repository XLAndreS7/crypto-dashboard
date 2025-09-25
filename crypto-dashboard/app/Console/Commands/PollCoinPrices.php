<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoinMarketCapService;
use App\Models\Crypto;
use App\Models\CryptoPrice;
use Carbon\Carbon;

class PollCoinPrices extends Command
{
    
    protected $signature = 'crypto:poll {--limit=10}';
    protected $description = 'Obtiene listados desde CoinMarketCap y persiste precios e instrumentos.';

    public function __construct(protected CoinMarketCapService $cmc)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Consultando CMC (limit={$limit})...");
        $payload = $this->cmc->getListings(); 

        if (!isset($payload['data']) || !is_array($payload['data'])) {
            $this->error('Respuesta inesperada de CMC');
            return self::FAILURE;
        }

        $rows = array_slice($payload['data'], 0, $limit);
        $now = Carbon::now();

        foreach ($rows as $row) {
           
            $crypto = Crypto::updateOrCreate(
                ['symbol' => $row['symbol']],
                ['name'   => $row['name']]
            );

           
            $usd = $row['quote']['USD'] ?? null;
            if ($usd) {
                CryptoPrice::create([
                    'crypto_id'   => $crypto->id,
                    'price_usd'   => $usd['price'],
                    'market_cap'  => $usd['market_cap'] ?? null,
                    'volume_24h'  => $usd['volume_24h'] ?? null,
                    'captured_at' => $now,
                ]);
            }
        }

        $this->info('¡Listo! Datos guardados.');
        return self::SUCCESS;
    }
}

