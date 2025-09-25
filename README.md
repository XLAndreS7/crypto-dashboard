# Crypto Dashboard (Laravel)

Panel simple para listar criptomonedas y ver sus precios actuales desde CoinMarketCap, con guardado en BD y auto-actualización en la UI.

## Requisitos
- PHP 8.2+
- Composer
- MySQL (en mi entorno: puerto 3309 con XAMPP)
- Extensión `curl` habilitada
- Cuenta/Key de CoinMarketCap (gratuita)

## Variables de entorno (.env)
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB (ajusta puerto si usas XAMPP en 3309)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3309
DB_DATABASE=crypto_dashboard
DB_USERNAME=root
DB_PASSWORD=

CMC_API_KEY=TU_API_KEY
CMC_BASE_URI=https://pro-api.coinmarketcap.com
