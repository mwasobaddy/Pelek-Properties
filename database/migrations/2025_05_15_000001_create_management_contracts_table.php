<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('contract_type'); // full_service, maintenance_only, financial_only
            $table->decimal('management_fee_percentage', 5, 2);
            $table->decimal('base_fee', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('payment_schedule'); // monthly, quarterly, yearly
            $table->json('services_included');
            $table->text('special_terms')->nullable();
            $table->string('status')->default('active'); // active, pending, expired, terminated
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_contracts');
    }
};
