<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankFieldsToBankManagementsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * Adds bank_name, branch_name and status to bank_managements table.
   */
  public function up()
  {
    Schema::table('bank_managements', function (Blueprint $table) {
      $table->string('bank_name')->nullable()->after('type');
      $table->string('branch_name')->nullable()->after('bank_name');
      $table->enum('status', ['active', 'inactive'])->default('active')->after('is_default');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down()
  {
    Schema::table('bank_managements', function (Blueprint $table) {
      $table->dropColumn(['bank_name', 'branch_name', 'status']);
    });
  }
}
