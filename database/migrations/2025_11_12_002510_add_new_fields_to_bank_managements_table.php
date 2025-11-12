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
    Schema::table('bank_managements', function (Blueprint $table) {
      $table->string('name')->nullable()->after('id');
      $table->string('account_holder_name')->nullable()->after('bank_name');
      $table->decimal('daily_max_amount', 15, 2)->nullable()->after('deposit_limit');
      $table->integer('daily_max_transaction_count')->nullable()->after('daily_max_amount');
      $table->decimal('max_transaction_amount', 15, 2)->nullable()->after('daily_max_transaction_count');
      $table->boolean('is_merchant_upi')->default(false)->after('upi_number');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('bank_managements', function (Blueprint $table) {
      $table->dropColumn([
        'name',
        'account_holder_name',
        'daily_max_amount',
        'daily_max_transaction_count',
        'max_transaction_amount',
        'is_merchant_upi'
      ]);
    });
  }
};
