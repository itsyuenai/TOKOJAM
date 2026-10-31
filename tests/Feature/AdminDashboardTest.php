<?php

namespace Tests\Feature;

use App\Models\BarangMasuk;
use App\Models\Order;
use App\Models\User;
use App\Models\Watch;
use App\Filament\Widgets\SalesStatsOverview;
use App\Filament\Widgets\SalesRevenueChart;
use App\Filament\Widgets\InventoryMovementChart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_sales_and_inventory_summaries(): void
    {
        $admin = User::factory()->create()->forceFill(['is_admin' => true]);
        $admin->save();
        $watch = Watch::query()->create([
            'name' => 'Jam Dashboard',
            'price' => 500000,
            'stock' => 3,
            'weight_gram' => 500,
            'sku' => 'DASHBOARD-WATCH',
        ]);
        $order = Order::query()->create([
            'customer_name' => 'Pelanggan Dashboard',
            'total_amount' => 1050000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'pos',
        ]);
        $order->orderItems()->create([
            'jam_id' => $watch->id,
            'quantity' => 2,
            'price_per_item' => 500000,
        ]);
        BarangMasuk::query()->create([
            'jam_id' => $watch->id,
            'quantity' => 5,
            'purchase_price' => 300000,
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('SalesStatsOverview');

        Livewire::actingAs($admin)
            ->test(SalesStatsOverview::class)
            ->assertSee('Ringkasan usaha')
            ->assertSee('Total penjualan produk')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Jam terjual')
            ->assertSee('Barang masuk')
            ->assertSee('Pesanan lunas');

        Livewire::actingAs($admin)
            ->test(SalesRevenueChart::class)
            ->assertSee('Grafik pemasukan penjualan');

        Livewire::actingAs($admin)
            ->test(InventoryMovementChart::class)
            ->assertSee('Pergerakan stok');
    }
}
