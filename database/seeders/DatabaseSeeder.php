<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(WatchCategorySeeder::class); // Run category seeder first
        $this->call(WatchSeeder::class); // Then run watch seeder
    }
}