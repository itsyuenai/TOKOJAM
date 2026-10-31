<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Watch;
use Illuminate\Support\Facades\DB;

final class InventoryStockService
{
    /**
     * @param  array<int, array{watch_id: int, delta: int}>  $movements
     */
    public function apply(array $movements): void
    {
        $deltas = [];

        foreach ($movements as $movement) {
            $watchId = $movement['watch_id'];
            $deltas[$watchId] = ($deltas[$watchId] ?? 0) + $movement['delta'];
        }

        DB::transaction(function () use ($deltas): void {
            $watches = Watch::query()
                ->whereIn('id', array_keys($deltas))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($deltas as $watchId => $delta) {
                if ($delta === 0) {
                    continue;
                }

                $watch = $watches->get($watchId);

                if (! $watch) {
                    throw new \RuntimeException('Produk inventaris tidak ditemukan.');
                }

                if ($delta < 0 && $watch->stock < abs($delta)) {
                    throw new InsufficientStockException(
                        productId: $watch->id,
                        productName: $watch->name,
                        requestedQuantity: abs($delta),
                        availableStock: $watch->stock,
                    );
                }

                $watch->increment('stock', $delta);
            }
        });
    }
}
