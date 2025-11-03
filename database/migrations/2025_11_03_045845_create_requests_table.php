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
    Schema::create('requests', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('mode'); // e.g. bank, upi, crypto
      $table->decimal('amount', 12, 2)->default(0);
      $table->decimal('payment_amount', 12, 2)->nullable();
      $table->string('utr')->nullable();
      $table->string('payment_from')->nullable();
      $table->string('account_upi')->nullable();
      $table->string('image')->nullable();
      $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
      $table->timestamp('accepted_at')->nullable();
      $table->timestamp('rejected_at')->nullable();
      $table->foreignId('assign_to')->nullable();
      $table->foreignId('assign_rejected_by')->nullable();
      $table->foreignId('created_by')->nullable();
      $table->foreignId('updated_by')->nullable();
      $table->foreignId('accepted_by')->nullable();
      $table->foreignId('rejected_by')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('requests');
  }
};
