<?php

use App\Enums\BillingUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->enum('billing_unit', array_column(BillingUnit::cases(), 'value'));
            $table->unsignedInteger('price_minor');
            $table->boolean('requires_finish');
            $table->unique(['id', 'business_id']);
            $table->index(['business_id', 'name']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
