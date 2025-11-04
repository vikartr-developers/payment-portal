<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('charge_backs', function (Blueprint $table) {
      $table->id();
      $table->string('merchant_name')->nullable();
      $table->unsignedBigInteger('user_id')->nullable(); // end-user related to payment request
      $table->unsignedBigInteger('request_id'); // Transaction ID -> payment request id
      $table->decimal('amount', 12, 2)->default(0);
      $table->text('reason')->nullable();
      $table->string('slip_path')->nullable();
      $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
      $table->dateTime('date')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->softDeletes();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
      $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('charge_backs');
  }
};
