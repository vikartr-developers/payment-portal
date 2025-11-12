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
    Schema::create('bank_sub_approvers', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('bank_management_id');
      $table->unsignedBigInteger('user_id');
      $table->timestamps();

      $table->foreign('bank_management_id')->references('id')->on('bank_managements')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

      $table->unique(['bank_management_id', 'user_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('bank_sub_approvers');
  }
};
