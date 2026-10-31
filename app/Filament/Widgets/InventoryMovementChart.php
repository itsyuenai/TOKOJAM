<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

final class InventoryMovementChart extends ChartWidget
{
    protected static ?string $heading = 'Pergerakan stok';

    protected static ?string $description = 'Jumlah unit barang masuk dan keluar selama 30 hari terakhir.';

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $today = CarbonImmutable::today();
        $startDate = $today->subDays(29);
        $start = $startDate->toDateString();
        $end = $today->toDateString();
        $incomingByDate = BarangMasuk::query()
            ->whereBetween('entry_date', [$start, $end])
            ->get(['entry_date', 'quantity'])
            ->groupBy(fn (BarangMasuk $movement): string => $movement->entry_date->toDateString());
        $outgoingByDate = BarangKeluar::query()
            ->whereBetween('exit_date', [$start, $end])
            ->get(['exit_date', 'quantity'])
            ->groupBy(fn (BarangKeluar $movement): string => $movement->exit_date->toDateString());

        $labels = [];
        $incoming = [];
        $outgoing = [];

        for ($offset = 0; $offset < 30; $offset++) {
            $date = $startDate->addDays($offset);
            $dateKey = $date->toDateString();
            $labels[] = $date->format('d/m');
            $incoming[] = (int) $incomingByDate->get($dateKey, collect())->sum('quantity');
            $outgoing[] = (int) $outgoingByDate->get($dateKey, collect())->sum('quantity');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang masuk (unit)',
                    'data' => $incoming,
                    'borderColor' => '#9c7a3d',
                    'backgroundColor' => 'rgba(156, 122, 61, 0.15)',
                    'fill' => true,
                    'tension' => 0.25,
                ],
                [
                    'label' => 'Barang keluar (unit)',
                    'data' => $outgoing,
                    'borderColor' => '#2f6f5e',
                    'backgroundColor' => 'rgba(47, 111, 94, 0.12)',
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
