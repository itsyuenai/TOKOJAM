<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Watch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class OnlineCheckoutService
{
    public function __construct(
        private readonly BiteshipService $biteship,
        private readonly MidtransService $midtrans,
    ) {}

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @return array<int, array{courier_company: string, courier_type: string, courier_name: string, price: int, duration: string}>
     */
    public function shippingRates(array $items, string $destinationPostalCode): array
    {
        [$normalized, $watches] = $this->loadAvailableItems($items);

        return $this->biteship->rates($watches, $normalized, $destinationPostalCode);
    }

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @param  array{customer_name: string, customer_email: string, customer_phone: string, shipping_address: string, shipping_postal_code: string, shipping_courier: string, shipping_service: string}  $customer
     */
    public function checkout(array $items, array $customer): Order
    {
        [$normalized, $watches] = $this->loadAvailableItems($items);
        $rates = $this->biteship->rates($watches, $normalized, $customer['shipping_postal_code']);
        $selectedRate = collect($rates)->first(fn (array $rate): bool => $rate['courier_company'] === $customer['shipping_courier']
            && $rate['courier_type'] === $customer['shipping_service']
        );

        if (! $selectedRate) {
            throw new RuntimeException('Tarif kurir sudah berubah. Periksa kembali pilihan pengiriman.');
        }

        $order = DB::transaction(function () use ($normalized, $customer, $selectedRate): Order {
            $lines = [];
            $subtotal = 0;

            foreach ($normalized as $item) {
                $watch = Watch::query()->whereKey($item['product_id'])->lockForUpdate()->firstOrFail();

                if ($watch->stock < $item['quantity']) {
                    throw new InsufficientStockException(
                        productId: $watch->id,
                        productName: $watch->name,
                        requestedQuantity: $item['quantity'],
                        availableStock: $watch->stock,
                    );
                }

                $price = (int) round((float) $watch->price);
                $subtotal += $price * $item['quantity'];
                $lines[] = ['watch' => $watch, 'quantity' => $item['quantity'], 'price' => $price];
            }

            $shippingFee = (int) $selectedRate['price'];
            $order = Order::create([
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'],
                'shipping_address' => $customer['shipping_address'],
                'shipping_postal_code' => $customer['shipping_postal_code'],
                'shipping_courier' => $selectedRate['courier_company'],
                'shipping_service' => $selectedRate['courier_type'],
                'shipping_fee' => $shippingFee,
                'total_amount' => $subtotal + $shippingFee,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'midtrans_snap',
                'tracking_token' => (string) Str::uuid(),
                'payment_expires_at' => now()->addHours(24),
            ]);

            $order->forceFill([
                'payment_reference' => 'TJM-'.$order->id.'-'.Str::upper(Str::random(8)),
            ])->save();

            foreach ($lines as $line) {
                $order->orderItems()->create([
                    'jam_id' => $line['watch']->id,
                    'quantity' => $line['quantity'],
                    'price_per_item' => $line['price'],
                ]);

                $line['watch']->decrement('stock', $line['quantity']);
            }

            return $order;
        });

        try {
            $payment = $this->midtrans->createPayment($order);
        } catch (Throwable $exception) {
            $this->releaseStock($order->id);
            $order->forceFill(['payment_status' => 'failed'])->save();
            throw $exception;
        }

        $order->forceFill(['payment_url' => $payment['redirect_url']])->save();

        return $order->refresh();
    }

    public function releaseStock(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = Order::query()->with('orderItems')->lockForUpdate()->findOrFail($orderId);

            if ($order->payment_status === 'paid' || $order->stock_released) {
                return;
            }

            foreach ($order->orderItems as $item) {
                Watch::query()->whereKey($item->jam_id)->lockForUpdate()->firstOrFail()
                    ->increment('stock', $item->quantity);
            }

            $order->forceFill([
                'status' => 'cancelled',
                'stock_released' => true,
            ])->save();
        });
    }

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @return array{0: array<int, array{product_id: int, quantity: int}>, 1: Collection<int, Watch>}
     */
    private function loadAvailableItems(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;

            if ($quantities[$productId] > 20) {
                throw new InvalidArgumentException('Maksimal pembelian untuk satu produk adalah 20 unit.');
            }
        }

        $normalized = [];
        foreach ($quantities as $productId => $quantity) {
            $normalized[] = ['product_id' => $productId, 'quantity' => $quantity];
        }

        $watches = Watch::query()
            ->whereIn('id', array_column($normalized, 'product_id'))
            ->get();

        if ($watches->count() !== count($normalized)) {
            throw new RuntimeException('Satu atau beberapa produk tidak lagi tersedia.');
        }

        return [$normalized, $watches];
    }
}
