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
        Schema::create('valuation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('property_type');
            $table->string('location');
            $table->decimal('land_size', 10, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->text('description')->nullable();
            $table->string('purpose')->default('sale'); // sale, rental, insurance
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->timestamps();
        });

        Schema::create('market_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('property_type');
            $table->decimal('average_price', 12, 2);
            $table->decimal('price_per_sqft', 10, 2);
            $table->integer('total_listings');
            $table->integer('days_on_market');
            $table->json('price_trends')->nullable();
            $table->timestamps();
        });

        Schema::create('valuation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valuation_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_analysis_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('estimated_value', 12, 2);
            $table->text('justification');
            $table->json('comparable_properties')->nullable();
            $table->json('valuation_factors');
            $table->string('confidence_level'); // high, medium, low
            $table->date('valid_until');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valuation_reports');
        Schema::dropIfExists('market_analyses');
        Schema::dropIfExists('valuation_requests');
    }
};
