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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Standard Polymorphic relation to attach log to specific model instances (BoxTemplate, Partner, BoxOrder, etc)
            $table->nullableMorphs('subject');

            $table->string('action'); // i.e 'created', 'updated', 'deleted', 'completed'
            $table->string('description'); // e.g 'Menambah partner PT ABC'
            $table->json('properties')->nullable(); // To store Before/After state payloads
            $table->string('ip_address')->nullable();

            $table->timestamps();

        // Helpful indexes for querying specific areas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};