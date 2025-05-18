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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type');
            $table->enum('listing_type', ['sale', 'rent', 'airbnb', 'commercial'])->default('sale');
            $table->decimal('price', 12, 2);
            $table->decimal('size', 10, 2)->nullable();
            $table->string('square_range')->nullable(); // For categorizing property sizes
            $table->string('location');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->boolean('available')->default(true);
            $table->string('whatsapp_number');
            $table->enum('status', ['available', 'under_contract', 'sold', 'rented'])->default('available');

            // Commercial-specific fields
            $table->enum('commercial_type', ['office', 'retail', 'industrial', 'warehouse', 'mixed_use'])->nullable();
            $table->string('commercial_size')->nullable();
            $table->string('commercial_price_monthly')->nullable();
            $table->string('commercial_price_annually')->nullable();
            $table->integer('floors')->nullable();
            $table->string('maintenance_status')->nullable();
            $table->date('last_renovation')->nullable();
            $table->boolean('has_parking')->default(false);
            $table->integer('parking_spaces')->nullable();
            $table->json('commercial_amenities')->nullable();
            $table->json('zoning_info')->nullable();
            $table->decimal('price_per_square_foot', 12, 2)->nullable();

            // Rental-specific fields
            $table->boolean('is_furnished')->default(false);
            $table->decimal('rental_price_daily', 12, 2)->nullable();
            $table->decimal('rental_price_monthly', 12, 2)->nullable();
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->text('lease_terms')->nullable();
            $table->json('utilities_included')->nullable();
            $table->date('available_from')->nullable();
            $table->integer('minimum_lease_period')->nullable();
            $table->json('rental_requirements')->nullable();
            $table->json('amenities_condition')->nullable();

            // Airbnb-specific fields
            $table->decimal('airbnb_price_nightly', 12, 2)->nullable();
            $table->decimal('airbnb_price_weekly', 12, 2)->nullable();
            $table->decimal('airbnb_price_monthly', 12, 2)->nullable();
            $table->json('availability_calendar')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->json('additional_features')->nullable();
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
