<?php

namespace App\Services;

use GuzzleHttp\Client;

class CoinMarketCapService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.coinmarketcap.base_uri'),
            'headers' => [
                'X-CMC_PRO_API_KEY' => config('services.coinmarketcap.key'),
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getListings()
    {
        $response = $this->client->get('/v1/cryptocurrency/listings/latest', [
            'query' => [
                'limit' => 10, 
                'convert' => 'USD',
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
