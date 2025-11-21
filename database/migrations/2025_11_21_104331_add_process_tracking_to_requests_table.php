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
    Schema::table('requests', function (Blueprint $table) {
      $table->enum('process_status', ['step1', 'step2', 'step3', 'completed', 'expired'])->default('step1')->after('status');
      $table->timestamp('started_at')->nullable()->after('process_status');
      $table->timestamp('expires_at')->nullable()->after('started_at');
      $table->string('session_id')->nullable()->after('expires_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('requests', function (Blueprint $table) {
      $table->dropColumn(['process_status', 'started_at', 'expires_at', 'session_id']);
    });
  }
};
