<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\BarangKeluar;
use App\Services\InventoryStockService;
use RuntimeException;

final class BarangKeluarObserver
{
    public function __construct(private readonly InventoryStockService $stock) {}

    public function created(BarangKeluar $movement): void
    {
        if ($movement->order_id !== null) {
            return;
        }

        $this->stock->apply([[
            'watch_id' => (int) $movement->jam_id,
            'delta' => -(int) $movement->quantity,
        ]]);
    }

    public function updated(BarangKeluar $movement): void
    {
        if ($movement->getOriginal('order_id') !== null || $movement->order_id !== null) {
            throw new RuntimeException('Catatan penjualan pesanan online tidak dapat diedit.');
        }

        $this->stock->apply([
            [
                'watch_id' => (int) $movement->getOriginal('jam_id'),
                'delta' => (int) $movement->getOriginal('quantity'),
            ],
            [
                'watch_id' => (int) $movement->jam_id,
                'delta' => -(int) $movement->quantity,
            ],
        ]);
    }

    public function deleted(BarangKeluar $movement): void
    {
        if ($movement->order_id !== null) {
            throw new RuntimeException('Catatan penjualan pesanan online tidak dapat dihapus.');
        }

        $this->stock->apply([[
            'watch_id' => (int) $movement->jam_id,
            'delta' => (int) $movement->quantity,
        ]]);
    }
}
