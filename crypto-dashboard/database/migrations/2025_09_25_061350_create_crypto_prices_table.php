<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_id')->constrained('cryptos')->onDelete('cascade');
            $table->decimal('price_usd', 18, 8);
            $table->decimal('market_cap', 20, 2)->nullable(); 
            $table->decimal('volume_24h', 20, 2)->nullable(); 
            $table->timestamp('captured_at'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_prices');
    }
};

