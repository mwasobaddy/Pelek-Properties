<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Sale-specific fields
            $table->boolean('is_for_sale')->default(false);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('sale_type')->nullable(); // direct sale, auction, etc.
            $table->text('sale_terms')->nullable();
            $table->boolean('mortgage_available')->default(false);
            $table->text('property_documents')->nullable(); // JSON array of required documents
            $table->string('ownership_type')->nullable(); // freehold, leasehold, etc.
            $table->string('development_status')->nullable(); // ready, under construction, off-plan
            $table->date('completion_date')->nullable();
            $table->integer('total_units')->nullable(); // For development projects
            $table->integer('available_units')->nullable();
            $table->boolean('has_title_deed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'is_for_sale',
                'sale_price',
                'sale_type',
                'sale_terms',
                'mortgage_available',
                'property_documents',
                'ownership_type',
                'development_status',
                'completion_date',
                'total_units',
                'available_units',
                'has_title_deed'
            ]);
        });
    }
};
