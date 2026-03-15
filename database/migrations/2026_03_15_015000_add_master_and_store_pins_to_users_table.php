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
        Schema::table('users', function (Blueprint $table) {
            $table->string('master_pin', 64)->nullable()->after('password');
            $table->string('store_pin', 64)->nullable()->after('master_pin');

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'pin')) {
                $table->dropColumn('pin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['master_pin', 'store_pin']);
            $table->enum('role', ['admin', 'employee'])->default('employee');
            $table->string('pin', 64)->nullable()->unique();
        });
    }
};