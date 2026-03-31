<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Starter, Growth, Enterprise
            $table->string('slug')->unique();
            $table->integer('max_users');
            $table->integer('max_stores')->default(50);
            $table->integer('max_warehouses')->default(1);
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('status', ['active', 'expired', 'cancelled', 'trial'])->default('trial');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->date('trial_ends_at')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
