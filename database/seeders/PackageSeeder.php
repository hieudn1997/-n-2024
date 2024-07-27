<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $numberOfPackages = DB::table('packages')->select('id')->count();

            $isDefault = $numberOfPackages === 0;

            DB::table('packages')->insert([
                'name' => 'Bảo toàn',
                'is_default' => $isDefault,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('packages')->insert([
                'name' => 'Ổn định',
                'is_default' => $isDefault,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('packages')->insert([
                'name' => 'Tăng trưởng',
                'is_default' => $isDefault,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            if (!$isDefault) {
                return;
            }

            DB::table('package_fund')->insert([
                'fund_id' => 1,
                'package_id' => 1,
                'allocation_percentage' => 100,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('package_fund')->insert([
                'fund_id' => 2,
                'package_id' => 2,
                'allocation_percentage' => 100,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('package_fund')->insert([
                'fund_id' => 3,
                'package_id' => 3,
                'allocation_percentage' => 100,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $th) {
            // throw $th;
        }
    }
}
