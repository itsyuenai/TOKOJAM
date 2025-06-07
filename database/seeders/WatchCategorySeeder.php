<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WatchCategory;

class WatchCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all categories that will be used by WatchSeeder
        WatchCategory::create(['name' => 'Casual Watches']);
        WatchCategory::create(['name' => 'Sport Watches']);
        WatchCategory::create(['name' => 'Luxury Watches']);
        WatchCategory::create(['name' => 'Smart Watches']);
        WatchCategory::create(['name' => 'Classic Watches']);
        WatchCategory::create(['name' => 'Vintage Watches']);
        WatchCategory::create(['name' => 'Chronograph Watches']);
    }
}