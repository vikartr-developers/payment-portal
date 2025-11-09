<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('trans_id')->unique();
            $table->string('account_holder_name');
            $table->string('account_number');
            $table->string('confirm_account_number');
            $table->string('branch_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('approver_status', ['approved', 'pending', 'rejected'])->default('pending');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
}
