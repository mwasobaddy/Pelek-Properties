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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_type_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->string('location');
            $table->string('neighborhood')->nullable();
            $table->string('city');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('size', 10, 2)->nullable(); // in square meters
            $table->enum('listing_type', ['sale', 'rent', 'airbnb']);
            $table->enum('status', ['available', 'under_contract', 'sold', 'rented'])->default('available');
            
            // Rental-specific fields
            $table->boolean('is_furnished')->default(false);
            $table->decimal('rental_price_daily', 12, 2)->nullable();
            $table->decimal('rental_price_monthly', 12, 2)->nullable();
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->text('lease_terms')->nullable();
            $table->json('utilities_included')->nullable();
            $table->date('available_from')->nullable();
            $table->integer('minimum_lease_period')->nullable(); // in months
            $table->json('rental_requirements')->nullable();
            $table->json('amenities_condition')->nullable();

            // Airbnb-specific fields
            $table->decimal('airbnb_price_nightly', 12, 2)->nullable();
            $table->decimal('airbnb_price_weekly', 12, 2)->nullable();
            $table->decimal('airbnb_price_monthly', 12, 2)->nullable();
            $table->json('availability_calendar')->nullable();
            
            $table->boolean('is_featured')->default(false);
            $table->json('additional_features')->nullable();
            $table->string('whatsapp_number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
