<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();
            $table->decimal('offer_amount', 12, 2);
            $table->string('payment_method'); // cash, mortgage, etc.
            $table->text('terms_conditions')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, rejected, expired
            $table->dateTime('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_offers');
    }
};
