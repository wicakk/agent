<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\GpsLog;
use App\Models\Product;
use App\Models\SalesActivity;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Plans ────────────────────────────────────────────────────────────
        $starter = SubscriptionPlan::create([
            'name' => 'Starter', 'slug' => 'starter',
            'max_users' => 5, 'max_stores' => 30, 'max_warehouses' => 1,
            'price_monthly' => 199000, 'price_yearly' => 1990000,
            'features' => ['Dashboard', 'Stok Gudang', 'Distribusi', 'Manajemen Toko', 'Max 5 Sales'],
            'is_active' => true,
        ]);

        $growth = SubscriptionPlan::create([
            'name' => 'Growth', 'slug' => 'growth',
            'max_users' => 20, 'max_stores' => 100, 'max_warehouses' => 3,
            'price_monthly' => 499000, 'price_yearly' => 4990000,
            'features' => ['Semua Starter', 'GPS Tracking', 'Export PDF/Excel', 'Max 20 Sales', 'Anti Fake GPS'],
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise', 'slug' => 'enterprise',
            'max_users' => 9999, 'max_stores' => 9999, 'max_warehouses' => 9999,
            'price_monthly' => 1499000, 'price_yearly' => 14990000,
            'features' => ['Semua Growth', 'Unlimited Users', 'Custom Domain', 'Priority Support', 'API Access'],
            'is_active' => true,
        ]);

        // ── Company ──────────────────────────────────────────────────────────
        $company = Company::create([
            'name'     => 'PT Demo Distribusi Nusantara',
            'slug'     => 'demo-distribusi',
            'email'    => 'info@demo-dist.com',
            'phone'    => '022-1234567',
            'address'  => 'Jl. Raya Bandung No. 123',
            'city'     => 'Bandung',
            'province' => 'Jawa Barat',
            'is_active'=> true,
        ]);

        Subscription::create([
            'company_id'    => $company->id,
            'plan_id'       => $growth->id,
            'status'        => 'active',
            'billing_cycle' => 'monthly',
            'starts_at'     => now()->subMonths(2),
            'ends_at'       => now()->addMonths(1),
            'amount_paid'   => 499000,
        ]);

        // ── Users ─────────────────────────────────────────────────────────────
        User::create([
            'company_id' => $company->id,
            'name'       => 'Budi Santoso (Owner)',
            'email'      => 'owner@demo.com',
            'phone'      => '08111222333',
            'password'   => Hash::make('password'),
            'role'       => 'owner',
            'is_active'  => true,
        ]);

        User::create([
            'company_id' => $company->id,
            'name'       => 'Rina Wijaya (Admin)',
            'email'      => 'admin@demo.com',
            'phone'      => '08222333444',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'is_active'  => true,
        ]);

        $salesList = [];
        foreach ([
            ['Andi Prasetyo',  'sales@demo.com',  '08333444555'],
            ['Deni Kurniawan', 'deni@demo.com',   '08444555666'],
            ['Siti Rahayu',    'siti@demo.com',   '08555666777'],
            ['Rizal Fauzi',    'rizal@demo.com',  '08666777888'],
        ] as [$name, $email, $phone]) {
            $salesList[] = User::create([
                'company_id' => $company->id,
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'password'   => Hash::make('password'),
                'role'       => 'sales',
                'is_active'  => true,
            ]);
        }

        // ── Warehouse ─────────────────────────────────────────────────────────
        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'name'       => 'Gudang Utama Bandung',
            'code'       => 'GDG-001',
            'address'    => 'Jl. Gudang Raya No. 45, Bandung',
            'city'       => 'Bandung',
            'latitude'   => -6.9175,
            'longitude'  => 107.6191,
            'pic_name'   => 'Pak Hendra',
            'pic_phone'  => '08777888999',
            'is_active'  => true,
        ]);

        // ── Products ──────────────────────────────────────────────────────────
        $productData = [
            ['Gudang Garam Surya 12', 'GGS-12', 'rokok',  'Gudang Garam', 500, 50,  21000, 23000],
            ['Gudang Garam Merah 12', 'GGM-12', 'rokok',  'Gudang Garam', 320, 40,  19500, 21500],
            ['Dji Sam Soe 12',        'DSS-12', 'rokok',  'Dji Sam Soe',  280, 30,  23000, 25500],
            ['Sampoerna Mild 16',     'SAM-16', 'rokok',  'Sampoerna',    450, 50,  24000, 27000],
            ['LA Bold 16',            'LAB-16', 'rokok',  'BAT',          380, 40,  22000, 24500],
            ['Indomie Goreng',        'IMG-01', 'mie',    'Indofood',     800, 100, 3000,  3500],
            ['Indomie Soto',          'IMS-01', 'mie',    'Indofood',     750, 100, 3000,  3500],
            ['Aqua 600ml',            'AQU-06', 'air',    'Danone',       600, 80,  3500,  4500],
            ['Teh Botol 350ml',       'TBS-35', 'minum',  'Sosro',        400, 50,  4000,  5000],
            ['Chitato Original',      'CHI-01', 'snack',  'Indofood',     8,   30,  8000,  10000], // Low stock intentional
        ];

        $products = [];
        foreach ($productData as [$name, $sku, $cat, $brand, $stock, $minStock, $buy, $sell]) {
            $products[] = Product::create([
                'company_id'    => $company->id,
                'warehouse_id'  => $warehouse->id,
                'name'          => $name,
                'sku'           => $sku,
                'category'      => $cat,
                'brand'         => $brand,
                'unit'          => 'pcs',
                'unit_per_pack' => 12,
                'stock_current' => $stock,
                'stock_minimum' => $minStock,
                'buy_price'     => $buy,
                'sell_price'    => $sell,
                'is_active'     => true,
            ]);
        }

        // ── Stores ────────────────────────────────────────────────────────────
        $storeData = [
            ['Warung Bu Asih',        'active',    0, 'Kiaracondong', -6.9248, 107.6424],
            ['Minimarket Sejahtera',  'active',    0, 'Antapani',     -6.9101, 107.6601],
            ['Toko Pak Darmo',        'active',    1, 'Cicadas',      -6.8991, 107.6501],
            ['Warung Pojok 99',       'potential', 1, 'Buahbatu',     -6.9501, 107.6401],
            ['Toko Mandiri Jaya',     'active',    2, 'Rancasari',    -6.9601, 107.6701],
            ['Minimarket Berkah',     'active',    2, 'Cibiru',       -6.9201, 107.7101],
            ['Warung Bu Sari',        'inactive',  3, 'Ujung Berung', -6.9101, 107.7201],
            ['Toko Sumber Rejeki',    'potential', 3, 'Dago',         -6.8801, 107.6101],
        ];

        $stores = [];
        foreach ($storeData as [$name, $status, $salesIdx, $district, $lat, $lng]) {
            $stores[] = Store::create([
                'company_id'      => $company->id,
                'user_id'         => $salesList[$salesIdx]->id,
                'name'            => $name,
                'address'         => 'Jl. ' . $district . ' No. ' . rand(1, 200),
                'city'            => 'Bandung',
                'district'        => $district,
                'latitude'        => $lat,
                'longitude'       => $lng,
                'status'          => $status,
                'store_type'      => rand(0, 1) ? 'warung' : 'minimarket',
                'last_visited_at' => now()->subDays(rand(1, 14)),
            ]);
        }

        // ── Distributions ──────────────────────────────────────────────────────
        $distData = [
            ['delivered',  0, 0],
            ['delivered',  1, 1],
            ['in_transit', 2, 2],
            ['pending',    3, 3],
            ['delivered',  0, 4],
        ];

        foreach ($distData as $i => [$status, $salesIdx, $storeIdx]) {
            $store = $stores[$storeIdx];
            $dist  = Distribution::create([
                'company_id'          => $company->id,
                'warehouse_id'        => $warehouse->id,
                'driver_id'           => $salesList[$salesIdx]->id,
                'store_id'            => $store->id,
                'destination_address' => $store->address,
                'destination_lat'     => $store->latitude,
                'destination_lng'     => $store->longitude,
                'status'              => $status,
                'scheduled_at'        => now()->subDays($i + 1),
                'departed_at'         => $status !== 'pending' ? now()->subDays($i + 1)->addHours(1) : null,
                'delivered_at'        => $status === 'delivered' ? now()->subDays($i)->addHours(3) : null,
            ]);

            foreach (array_slice($products, $i * 2, 3) as $prod) {
                DistributionItem::create([
                    'distribution_id'    => $dist->id,
                    'product_id'         => $prod->id,
                    'quantity_requested' => rand(10, 30),
                    'quantity_delivered' => $status === 'delivered' ? rand(10, 30) : 0,
                    'unit_price'         => $prod->sell_price,
                ]);
            }
        }

        // ── Activities & Transactions ─────────────────────────────────────────
        foreach ($salesList as $sales) {
            for ($d = 6; $d >= 0; $d--) {
                $numVisits = rand(2, 4);
                for ($v = 0; $v < $numVisits; $v++) {
                    $store   = $stores[array_rand($stores)];
                    $actDate = now()->subDays($d)
                        ->setHour(rand(8, 16))
                        ->setMinute(rand(0, 59))
                        ->setSecond(0);

                    $activity = SalesActivity::create([
                        'company_id'  => $company->id,
                        'user_id'     => $sales->id,
                        'store_id'    => $store->id,
                        'type'        => 'check_in',
                        'latitude'    => $store->latitude  + (rand(-10, 10) / 10000),
                        'longitude'   => $store->longitude + (rand(-10, 10) / 10000),
                        'accuracy'    => rand(5, 25),
                        'activity_at' => $actDate,
                        'created_at'  => $actDate,
                        'updated_at'  => $actDate,
                    ]);

                    // GPS log
                    GpsLog::create([
                        'company_id'  => $company->id,
                        'user_id'     => $sales->id,
                        'latitude'    => $store->latitude  + (rand(-5, 5) / 10000),
                        'longitude'   => $store->longitude + (rand(-5, 5) / 10000),
                        'accuracy'    => rand(5, 20),
                        'speed'       => rand(0, 40),
                        'is_mock_location' => false,
                        'is_location_jump' => false,
                        'logged_at'   => $actDate,
                    ]);

                    // Create transaction (60% chance)
                    if (rand(0, 9) < 6) {
                        $txProducts = array_slice($products, rand(0, 6), rand(1, 3));
                        $subtotal   = 0;
                        $txItems    = [];

                        foreach ($txProducts as $prod) {
                            $qty      = rand(5, 25);
                            $sub      = $qty * $prod->sell_price;
                            $subtotal += $sub;
                            $txItems[] = ['prod' => $prod, 'qty' => $qty, 'sub' => $sub];
                        }

                        $tx = Transaction::create([
                            'company_id'     => $company->id,
                            'user_id'        => $sales->id,
                            'store_id'       => $store->id,
                            'activity_id'    => $activity->id,
                            'type'           => 'sale',
                            'status'         => rand(0, 3) > 0 ? 'paid' : 'confirmed',
                            'subtotal'       => $subtotal,
                            'discount'       => 0,
                            'tax'            => 0,
                            'total'          => $subtotal,
                            'payment_method' => rand(0, 1) ? 'cash' : 'transfer',
                            'paid_at'        => now()->subDays($d),
                            'created_at'     => $actDate,
                            'updated_at'     => $actDate,
                        ]);

                        foreach ($txItems as $item) {
                            TransactionItem::create([
                                'transaction_id' => $tx->id,
                                'product_id'     => $item['prod']->id,
                                'quantity'       => $item['qty'],
                                'unit_price'     => $item['prod']->sell_price,
                                'discount'       => 0,
                                'subtotal'       => $item['sub'],
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('');
        $this->command->info('✅  Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('🔑  Login credentials:');
        $this->command->info('    Owner : owner@demo.com  / password');
        $this->command->info('    Admin : admin@demo.com  / password');
        $this->command->info('    Sales : sales@demo.com  / password');
        $this->command->info('');
    }
}
