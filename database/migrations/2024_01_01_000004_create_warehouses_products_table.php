<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('pic_name')->nullable(); // Person In Charge
            $table->string('pic_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category')->nullable(); // rokok, minuman, snack
            $table->string('brand')->nullable();
            $table->string('unit')->default('pcs'); // pcs, pack, karton
            $table->integer('unit_per_pack')->default(1);
            $table->integer('stock_current')->default(0);
            $table->integer('stock_minimum')->default(10);
            $table->decimal('buy_price', 12, 2)->default(0);
            $table->decimal('sell_price', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment', 'return']);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('reference_no')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
    }
};
