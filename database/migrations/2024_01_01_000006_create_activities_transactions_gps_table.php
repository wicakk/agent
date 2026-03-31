<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Sales
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['check_in', 'check_out', 'order', 'payment_collection', 'survey']);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->decimal('accuracy', 8, 2)->nullable(); // meters
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('activity_at');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Sales
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('sales_activities')->nullOnDelete();
            $table->string('invoice_no')->unique();
            $table->enum('type', ['sale', 'return', 'consignment']);
            $table->enum('status', ['draft', 'confirmed', 'delivered', 'paid', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'transfer', 'credit', 'tempo'])->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('gps_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('altitude', 8, 2)->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->boolean('is_location_jump')->default(false); // Anomaly detection
            $table->decimal('distance_from_previous', 10, 2)->nullable(); // meters
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['user_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sales_activities');
    }
};
