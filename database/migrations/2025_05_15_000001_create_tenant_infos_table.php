<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantInfosTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('tenant_name');
            $table->string('tenant_phone')->nullable();
            $table->string('tenant_email')->nullable();
            $table->date('lease_start');
            $table->date('lease_end');
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->enum('payment_status', ['pending', 'paid', 'late', 'defaulted'])->default('pending');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_infos');
    }
};
