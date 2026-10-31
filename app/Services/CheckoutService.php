<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Watch;
use Illuminate\Support\Facades\DB;

final class CheckoutService
{
    public function __construct(private readonly SalesLedgerService $salesLedger) {}

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     */
    public function process(array $items, ?string $customerName = null): Order
    {
        return DB::transaction(function () use ($items, $customerName): Order {
            $lines = [];
            $totalAmount = 0.0;

            foreach ($this->normalizeItems($items) as $item) {
                $watch = Watch::query()
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($watch->stock < $item['quantity']) {
                    throw new InsufficientStockException(
                        productId: $watch->id,
                        productName: $watch->name,
                        requestedQuantity: $item['quantity'],
                        availableStock: $watch->stock,
                    );
                }

                $price = (float) $watch->price;
                $totalAmount += $price * $item['quantity'];

                $lines[] = [
                    'watch' => $watch,
                    'quantity' => $item['quantity'],
                    'price_per_item' => $price,
                ];
            }

            $order = Order::create([
                'customer_name' => $customerName ?: 'Guest',
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'pos',
            ]);

            foreach ($lines as $line) {
                /** @var Watch $watch */
                $watch = $line['watch'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'jam_id' => $watch->id,
                    'quantity' => $line['quantity'],
                    'price_per_item' => $line['price_per_item'],
                ]);

                $watch->decrement('stock', $line['quantity']);
            }

            $this->salesLedger->recordPaidOrder($order);

            return $order->load('orderItems');
        });
    }

    /**
     * Combine repeated product IDs so each watch is checked and decremented once.
     *
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function normalizeItems(array $items): array
    {
        $quantitiesByProduct = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $quantity;
        }

        return array_values(array_map(
            static fn (int $quantity, int $productId): array => [
                'product_id' => $productId,
                'quantity' => $quantity,
            ],
            $quantitiesByProduct,
            array_keys($quantitiesByProduct),
        ));
    }
}
