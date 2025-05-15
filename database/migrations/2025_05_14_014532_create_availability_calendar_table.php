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
        Schema::create('availability_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['available', 'booked', 'blocked', 'maintenance']);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->json('notes')->nullable();
            $table->timestamps();

            // Ensure we don't have duplicate dates for the same property
            $table->unique(['property_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_calendar');
    }
};
