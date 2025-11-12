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
    Schema::table('withdrawal_requests', function (Blueprint $table) {
      // Ensure created_by, updated_by, deleted_by are nullable
      $table->unsignedBigInteger('created_by')->nullable()->change();
      $table->unsignedBigInteger('updated_by')->nullable()->change();
      $table->unsignedBigInteger('deleted_by')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('withdrawal_requests', function (Blueprint $table) {
      // Revert changes if needed (usually not required for nullable changes)
    });
  }
};
