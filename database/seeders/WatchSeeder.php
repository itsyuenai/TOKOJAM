<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Watch;
use App\Models\WatchCategory; // Import the WatchCategory model

class WatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch categories to get their IDs
        $smartId = WatchCategory::where('name', 'Smart Watches')->first()->id;
        $sportId = WatchCategory::where('name', 'Sport Watches')->first()->id;
        $classicId = WatchCategory::where('name', 'Classic Watches')->first()->id;
        $luxuryId = WatchCategory::where('name', 'Luxury Watches')->first()->id;
        $vintageId = WatchCategory::where('name', 'Vintage Watches')->first()->id;
        $chronographId = WatchCategory::where('name', 'Chronograph Watches')->first()->id;

        Watch::create(['name' => 'Rolex Submariner', 'stock' => 3, 'price' => 250000000, 'category_id' => $luxuryId]);
        Watch::create(['name' => 'Casio G-Shock GA-700', 'stock' => 50, 'price' => 1850000, 'category_id' => $sportId]);
        Watch::create(['name' => 'Tag Heuer Formula 1', 'stock' => 8, 'price' => 33500000, 'category_id' => $sportId]);
        Watch::create(['name' => 'Orient Envoy Open Heart', 'stock' => 20, 'price' => 4121000, 'category_id' => $classicId]);
        Watch::create(['name' => 'Digitec Nexus', 'stock' => 100, 'price' => 1218000, 'category_id' => $smartId]);
        Watch::create(['name' => 'Seiko Prospex Speedtimer', 'stock' => 5, 'price' => 7870000, 'category_id' => $chronographId]);
        Watch::create(['name' => 'Tissot Gentleman Powermatic 80', 'stock' => 15, 'price' => 15150000, 'category_id' => $classicId]);
        Watch::create(['name' => 'Vintage Omega Seamaster', 'stock' => 7, 'price' => 112000000, 'category_id' => $vintageId]);
        // Add more as needed
    }
}