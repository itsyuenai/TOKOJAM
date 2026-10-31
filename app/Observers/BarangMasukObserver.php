<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\BarangMasuk;
use App\Services\InventoryStockService;

final class BarangMasukObserver
{
    public function __construct(private readonly InventoryStockService $stock) {}

    public function created(BarangMasuk $movement): void
    {
        $this->stock->apply([[
            'watch_id' => (int) $movement->jam_id,
            'delta' => (int) $movement->quantity,
        ]]);
    }

    public function updated(BarangMasuk $movement): void
    {
        $oldWatchId = (int) $movement->getOriginal('jam_id');
        $oldQuantity = (int) $movement->getOriginal('quantity');

        $this->stock->apply([
            ['watch_id' => $oldWatchId, 'delta' => -$oldQuantity],
            ['watch_id' => (int) $movement->jam_id, 'delta' => (int) $movement->quantity],
        ]);
    }

    public function deleted(BarangMasuk $movement): void
    {
        $this->stock->apply([[
            'watch_id' => (int) $movement->jam_id,
            'delta' => -(int) $movement->quantity,
        ]]);
    }
}
