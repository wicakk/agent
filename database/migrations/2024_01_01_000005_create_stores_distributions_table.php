<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Sales assigned
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'potential', 'inactive'])->default('potential');
            $table->string('store_type')->nullable(); // minimarket, warung, supermarket
            $table->date('last_visited_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_no')->unique();
            $table->enum('status', ['pending', 'in_transit', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->text('destination_address')->nullable();
            $table->decimal('destination_lat', 10, 8)->nullable();
            $table->decimal('destination_lng', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('proof_photo')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_requested');
            $table->integer('quantity_delivered')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
        Schema::dropIfExists('distributions');
        Schema::dropIfExists('stores');
    }
};
