<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BarangKeluar;
use App\Models\Order;
use RuntimeException;

final class SalesLedgerService
{
    public function recordPaidOrder(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            throw new RuntimeException('Penjualan hanya dapat dicatat setelah pembayaran terkonfirmasi.');
        }

        $order->loadMissing('orderItems');

        foreach ($order->orderItems as $item) {
            BarangKeluar::query()->firstOrCreate(
                [
                    'order_id' => $order->id,
                    'jam_id' => $item->jam_id,
                ],
                [
                    'quantity' => $item->quantity,
                    'customer_name' => $order->customer_name,
                    'sale_price' => $item->price_per_item,
                    'exit_date' => now()->toDateString(),
                ],
            );
        }
    }
}
