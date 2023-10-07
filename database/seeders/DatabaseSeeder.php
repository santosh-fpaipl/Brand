<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Database\Seeders\StockSeeder;
use Database\Seeders\SupplierSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      //\App\Models\User::factory(10)->create();

      $this->call(SupplierSeeder::class);

      $this->call(StockSeeder::class);

      $this->call(JobWorkOrderSeeder::class);

    }
}