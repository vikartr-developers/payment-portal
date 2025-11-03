<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email'); // e.g., Admin, Editor
            $table->string('current_plan')->nullable()->after('role'); // e.g., Basic, Premium
            $table->string('billing')->nullable()->after('current_plan'); // e.g., Monthly
            $table->unsignedTinyInteger('status')->default(1)->after('billing'); // 1=Pending, 2=Active, 3=Inactive
            $table->string('avatar')->nullable()->after('status'); // avatar filename
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'current_plan', 'billing', 'status', 'avatar']);
        });
    }
};
