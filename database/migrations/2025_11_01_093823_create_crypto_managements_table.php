<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('crypto_managements', function (Blueprint $table) {
      $table->id();
      $table->string('wallet_address')->unique();
      $table->string('network');          // e.g. Ethereum, Binance Smart Chain
      $table->string('coin');             // e.g. ETH, BNB
      $table->string('created_by');             // e.g. ETH, BNB
      $table->string('updated_by');             // e.g. ETH, BNB
      $table->enum('status', ['active', 'inactive'])->default('active');
      $table->boolean('is_default')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('crypto_managements');
  }
};
