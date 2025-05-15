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
        Schema::table('properties', function (Blueprint $table) {
            // Airbnb specific fields
            $table->decimal('nightly_rate', 10, 2)->nullable();
            $table->decimal('weekly_rate', 10, 2)->nullable();
            $table->decimal('monthly_rate', 10, 2)->nullable();
            $table->integer('max_guests')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->json('check_in_instructions')->nullable();
            $table->json('house_rules')->nullable();
            $table->boolean('instant_booking')->default(false);
            $table->json('cancellation_policy')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'nightly_rate',
                'weekly_rate',
                'monthly_rate',
                'max_guests',
                'bedrooms',
                'bathrooms',
                'check_in_instructions',
                'house_rules',
                'instant_booking',
                'cancellation_policy'
            ]);
        });
    }
};
