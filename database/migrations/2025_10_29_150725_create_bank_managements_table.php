<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankManagementsTable extends Migration
{
  public function up()
  {
    Schema::create('bank_managements', function (Blueprint $table) {
      $table->id();
      $table->enum('type', ['bank', 'upi']);

      // Bank fields
      $table->string('account_number')->nullable();
      $table->string('ifsc_code')->nullable();

      // UPI fields
      $table->string('upi_id')->nullable();
      $table->string('upi_number')->nullable();

      // Common fields
      $table->decimal('deposit_limit', 15, 2)->default(0);
      $table->boolean('is_default')->default(false);

      // Tracking fields
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();

      // Soft delete and timestamps
      $table->softDeletes();
      $table->timestamps();

      // Foreign keys (optional, uncomment if you have a users table)
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
    });
  }

  public function down()
  {
    Schema::dropIfExists('bank_managements');
  }
}
