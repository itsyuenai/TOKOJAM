<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

final class SalesRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik pemasukan penjualan';

    protected static ?string $description = 'Nilai barang pada pesanan lunas, tidak termasuk ongkir, selama 30 hari terakhir.';

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $today = CarbonImmutable::today();
        $startDate = $today->subDays(29);
        $itemsByDate = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
            ->whereBetween('created_at', [$startDate->startOfDay(), $today->endOfDay()])
            ->get(['created_at', 'quantity', 'price_per_item'])
            ->groupBy(fn (OrderItem $item): string => $item->created_at->toDateString());

        $labels = [];
        $amounts = [];

        for ($offset = 0; $offset < 30; $offset++) {
            $date = $startDate->addDays($offset);
            $labels[] = $date->format('d/m');
            $amounts[] = (int) round($itemsByDate->get($date->toDateString(), collect())->sum(
                fn (OrderItem $item): float => (float) $item->price_per_item * (int) $item->quantity,
            ));
        }

        return [
            'datasets' => [[
                'label' => 'Penjualan produk (Rp)',
                'data' => $amounts,
                'backgroundColor' => '#2f6f5e',
                'borderRadius' => 6,
            ]],
            'labels' => $labels,
        ];
    }
}
