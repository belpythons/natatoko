<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds Mayar.id payment columns to box_orders table.
     */
    public function up(): void
    {
        Schema::table('box_orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('total_price');
            $table->string('payment_status')->default('paid')->after('payment_method');
            $table->string('mayar_link', 500)->nullable()->after('payment_status');
            $table->string('mayar_transaction_id')->nullable()->after('mayar_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('box_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'mayar_link',
                'mayar_transaction_id',
            ]);
        });
    }
};
