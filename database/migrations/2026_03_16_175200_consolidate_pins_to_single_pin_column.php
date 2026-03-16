<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Consolidate master_pin and store_pin into a single `pin` column.
     * Uses Bcrypt hashing (via Laravel Hash facade), so column needs 255 chars.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add unified pin column if it doesn't already exist
            if (!Schema::hasColumn('users', 'pin')) {
                $table->string('pin', 255)->nullable()->after('password');
            }

            // Drop the redundant split columns
            if (Schema::hasColumn('users', 'master_pin')) {
                $table->dropColumn('master_pin');
            }
            if (Schema::hasColumn('users', 'store_pin')) {
                $table->dropColumn('store_pin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'master_pin')) {
                $table->string('master_pin', 255)->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'store_pin')) {
                $table->string('store_pin', 255)->nullable()->after('master_pin');
            }
            if (Schema::hasColumn('users', 'pin')) {
                $table->dropColumn('pin');
            }
        });
    }
};
