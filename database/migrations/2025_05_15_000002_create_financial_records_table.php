<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // income, expense
            $table->string('category'); // rent, maintenance, utilities, management_fee, etc.
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};
