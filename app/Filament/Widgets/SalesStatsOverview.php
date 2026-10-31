<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\BarangMasuk;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class SalesStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan usaha';

    protected ?string $description = 'Total akumulasi penjualan yang sudah dibayar dan pergerakan stok.';

    protected function getStats(): array
    {
        $paidOrders = Order::query()->where('payment_status', 'paid');
        $revenue = (float) OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
            ->sum(DB::raw('quantity * price_per_item'));
        $paidOrderCount = (clone $paidOrders)->count();
        $soldQuantity = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
            ->sum('quantity');
        $incomingQuantity = BarangMasuk::query()->sum('quantity');

        return [
            Stat::make('Total penjualan produk', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Nilai barang pada pesanan lunas, tidak termasuk ongkir')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Jam terjual', number_format((int) $soldQuantity, 0, ',', '.'))
                ->description('Jumlah barang pada pesanan yang telah dibayar')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),
            Stat::make('Barang masuk', number_format((int) $incomingQuantity, 0, ',', '.'))
                ->description('Total unit stok yang pernah diterima')
                ->descriptionIcon('heroicon-m-archive-box-arrow-down')
                ->color('warning'),
            Stat::make('Pesanan lunas', number_format($paidOrderCount, 0, ',', '.'))
                ->description('Transaksi dengan pembayaran terkonfirmasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
