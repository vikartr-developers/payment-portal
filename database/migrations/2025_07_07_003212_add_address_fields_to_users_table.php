<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_line_1')->after('email');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('state_name')->after('address_line_2');
            $table->string('zip_code')->after('state_name');
            $table->string('country_code', 5)->after('zip_code');
            $table->softDeletes()->after('remember_token'); // Adds deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address_line_1',
                'address_line_2',
                'state_id',
                'zip_code',
                'country_code',
                'deleted_at',
            ]);
        });
    }
};
