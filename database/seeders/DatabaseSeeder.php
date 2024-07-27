<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CredentialSeeder::class,
            FundSeeder::class,
            PackageSeeder::class
        ]);
    }
}
