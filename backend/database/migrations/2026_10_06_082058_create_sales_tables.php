<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('branch_id');
            $table->foreign(['branch_id', 'business_id'])->references(['id', 'business_id'])->on('branches')->restrictOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign(['customer_id', 'business_id'])->references(['id', 'business_id'])->on('customers')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->uuid('request_key');
            $table->char('request_hash', 64);
            $table->unique(['branch_id', 'request_key']);
            $table->unique(['id', 'business_id']);
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30)->nullable();
            $table->string('business_name', 120);
            $table->string('branch_name', 120);
            $table->char('currency', 3);
            $table->string('timezone', 64);
            $table->unsignedBigInteger('total_minor');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'created_at']);
        });
        Schema::create('sale_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('sale_id');
            $table->foreign(['sale_id', 'business_id'])->references(['id', 'business_id'])->on('sales')->restrictOnDelete();
            $table->unsignedBigInteger('service_id');
            $table->foreign(['service_id', 'business_id'])->references(['id', 'business_id'])->on('services')->restrictOnDelete();
            $table->string('service_name', 120);
            $table->enum('billing_unit', ['piece', 'kg']);
            $table->unsignedInteger('quantity_milli');
            $table->unsignedInteger('price_minor');
            $table->unsignedBigInteger('total_minor');
            $table->boolean('requires_finish');
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('sale_id');
            $table->foreign(['sale_id', 'business_id'])->references(['id', 'business_id'])->on('sales')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->uuid('request_key');
            $table->unique(['sale_id', 'request_key']);
            $table->unsignedBigInteger('amount_minor');
            $table->enum('method', ['cash', 'transfer', 'card']);
            $table->string('reference', 120)->nullable();
            $table->timestamps();
        });
        Schema::create('sale_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('sale_id');
            $table->foreign(['sale_id', 'business_id'])->references(['id', 'business_id'])->on('sales')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 40);
            $table->string('details', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
    }
};
