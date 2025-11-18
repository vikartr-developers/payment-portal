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
      $table->string('short_code', 20)->unique()->nullable()->after('id');
    });
  }


  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('bank_managements', function (Blueprint $table) {
      $table->dropColumn('short_code');
    });
  }
};
