<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommercialLeasesTable extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_name');
            $table->string('tenant_business');
            $table->string('tenant_contact');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_rate', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->enum('lease_type', ['net', 'gross', 'modified_gross'])->default('net');
            $table->json('terms_conditions');
            $table->integer('duration_months');
            $table->enum('status', ['active', 'pending', 'expired', 'terminated'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_leases');
    }
}
