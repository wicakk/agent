<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');                      // "Cabang Bandung"
            $table->string('code')->nullable();          // "CBG-01"
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('pic_name')->nullable();      // Person In Charge
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add branch_id to users (admin/sales belong to a branch)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')
                  ->nullable()
                  ->after('company_id')
                  ->constrained('branches')
                  ->nullOnDelete();
        });

        // Add branch_id to all operational tables
        $tables = [
            'warehouses', 'products', 'stores',
            'distributions', 'sales_activities',
            'transactions', 'gps_logs',
        ];

        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->after('company_id')
                      ->constrained('branches')
                      ->nullOnDelete();
            });
        }

        // stock_movements is linked via product (inherits branch)
        // distribution_items is linked via distribution (inherits branch)
        // transaction_items is linked via transaction (inherits branch)
        // subscriptions stay at company level (Owner manages)
    }

    public function down(): void
    {
        $tables = [
            'gps_logs', 'transactions', 'sales_activities',
            'distributions', 'stores', 'products',
            'warehouses', 'users',
        ];
        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
        Schema::dropIfExists('branches');
    }
};
