<?php

namespace Database\Seeders;

use App\Models\Watch;
use App\Models\WatchCategory;
use Illuminate\Database\Seeder;

class DemoWatchCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $category = WatchCategory::firstOrCreate(['name' => 'Koleksi Pilihan']);

        $watches = [
            [
                'sku' => 'DEMO-SMART-001',
                'name' => 'Smartwatch Minimal',
                'price' => 1299000,
                'stock' => 12,
                'weight_gram' => 250,
                'image' => 'images/watches/smartwatch-minimal.jpg',
                'description' => 'Jam pintar dengan tampilan modern untuk menemani aktivitas harian.',
            ],
            [
                'sku' => 'DEMO-CLASSIC-001',
                'name' => 'Classic Leather Watch',
                'price' => 2450000,
                'stock' => 8,
                'weight_gram' => 300,
                'image' => 'images/watches/classic-leather-watch.jpg',
                'description' => 'Jam klasik dengan strap kulit dan dial minimalis.',
            ],
            [
                'sku' => 'DEMO-CHRONO-001',
                'name' => 'Chronograph Heritage',
                'price' => 3850000,
                'stock' => 5,
                'weight_gram' => 350,
                'image' => 'images/watches/chronograph-heritage.jpg',
                'description' => 'Jam chronograph bergaya heritage dengan detail dial yang elegan.',
            ],
        ];

        foreach ($watches as $watch) {
            Watch::updateOrCreate(
                ['sku' => $watch['sku']],
                [...$watch, 'category_id' => $category->id],
            );
        }
    }
}
