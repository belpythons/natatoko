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
        Schema::table('box_orders', function (Blueprint $table) {
            $table->foreignId('shop_session_id')
                  ->nullable()
                  ->constrained('shop_sessions')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('box_orders', function (Blueprint $table) {
            $table->dropForeign(['shop_session_id']);
            $table->dropColumn('shop_session_id');
        });
    }
};
