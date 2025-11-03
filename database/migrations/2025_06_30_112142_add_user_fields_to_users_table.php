<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            // $table->string('role')->default('subscriber'); // Consider normalizing roles later
            // $table->string('current_plan')->nullable();
            // $table->decimal('billing', 10, 2)->nullable();
            // $table->tinyInteger('status')->default(1); // 1 = pending, 2 = active, 3 = inactive
            // $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
