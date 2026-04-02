<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'demo-distribusi')->first();
        if (!$company) {
            $this->command->warn('Demo company not found. Run DatabaseSeeder first.');
            return;
        }

        // Create 3 branches
        $bandung = Branch::create([
            'company_id' => $company->id,
            'name'       => 'Cabang Bandung',
            'code'       => 'CBG',
            'city'       => 'Bandung',
            'address'    => 'Jl. Raya Bandung No. 123',
            'phone'      => '022-1234567',
            'pic_name'   => 'Budi Santoso',
            'is_active'  => true,
        ]);

        $jakarta = Branch::create([
            'company_id' => $company->id,
            'name'       => 'Cabang Jakarta',
            'code'       => 'JKT',
            'city'       => 'Jakarta',
            'address'    => 'Jl. Sudirman No. 45',
            'phone'      => '021-9876543',
            'pic_name'   => 'Rina Wijaya',
            'is_active'  => true,
        ]);

        $surabaya = Branch::create([
            'company_id' => $company->id,
            'name'       => 'Cabang Surabaya',
            'code'       => 'SBY',
            'city'       => 'Surabaya',
            'address'    => 'Jl. Pahlawan No. 7',
            'phone'      => '031-5556789',
            'pic_name'   => 'Deni Kurniawan',
            'is_active'  => true,
        ]);

        // Assign existing users to branches
        // Owner stays unassigned (sees all)
        User::where('company_id', $company->id)
            ->where('email', 'admin@demo.com')
            ->update(['branch_id' => $bandung->id]);

        User::where('company_id', $company->id)
            ->whereIn('email', ['sales@demo.com', 'deni@demo.com'])
            ->update(['branch_id' => $bandung->id]);

        User::where('company_id', $company->id)
            ->whereIn('email', ['siti@demo.com'])
            ->update(['branch_id' => $jakarta->id]);

        User::where('company_id', $company->id)
            ->whereIn('email', ['rizal@demo.com'])
            ->update(['branch_id' => $surabaya->id]);

        // Create admin users for Jakarta and Surabaya
        User::create([
            'company_id' => $company->id,
            'branch_id'  => $jakarta->id,
            'name'       => 'Admin Jakarta',
            'email'      => 'admin.jakarta@demo.com',
            'phone'      => '08777111222',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'is_active'  => true,
        ]);

        User::create([
            'company_id' => $company->id,
            'branch_id'  => $surabaya->id,
            'name'       => 'Admin Surabaya',
            'email'      => 'admin.sby@demo.com',
            'phone'      => '08888222333',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'is_active'  => true,
        ]);

        // Update existing operational data to include branch_id
        // (For demo: assign Bandung branch to existing data)
        DB::table('warehouses')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('products')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('stores')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('distributions')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('sales_activities')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('transactions')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);
        DB::table('gps_logs')->where('company_id', $company->id)->update(['branch_id' => $bandung->id]);

        $this->command->info('');
        $this->command->info('✅  Branches seeded:');
        $this->command->info('   Cabang Bandung  → admin@demo.com, sales@demo.com, deni@demo.com');
        $this->command->info('   Cabang Jakarta  → admin.jakarta@demo.com, siti@demo.com');
        $this->command->info('   Cabang Surabaya → admin.sby@demo.com, rizal@demo.com');
        $this->command->info('   Owner           → owner@demo.com (lihat semua cabang)');
    }
}
