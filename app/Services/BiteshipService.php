<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Watch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class BiteshipService
{
    /**
     * @param  Collection<int, Watch>  $watches
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @return array<int, array{courier_company: string, courier_type: string, courier_name: string, price: int, duration: string}>
     */
    public function rates(Collection $watches, array $items, string $destinationPostalCode): array
    {
        $this->ensureConfigured();

        $watchesById = $watches->keyBy('id');
        $requestItems = [];

        foreach ($items as $item) {
            $watch = $watchesById->get((int) $item['product_id']);

            if (! $watch) {
                throw new RuntimeException('Produk untuk pengiriman tidak ditemukan.');
            }

            $requestItems[] = [
                'name' => $watch->name,
                'value' => (int) round((float) $watch->price),
                'quantity' => (int) $item['quantity'],
                'weight' => (int) $watch->weight_gram,
            ];
        }

        $response = $this->client()
            ->post('https://api.biteship.com/v1/rates/couriers', [
                'origin_postal_code' => config('services.biteship.origin_postal_code'),
                'destination_postal_code' => $destinationPostalCode,
                'couriers' => config('services.biteship.couriers'),
                'items' => $requestItems,
            ])
            ->throw()
            ->json();

        $pricing = $response['pricing'] ?? null;

        if (! is_array($pricing)) {
            throw new RuntimeException('Biteship tidak mengembalikan daftar tarif pengiriman.');
        }

        return collect($pricing)
            ->filter(fn ($rate): bool => is_array($rate)
                && is_string($rate['courier_company'] ?? null)
                && is_string($rate['courier_type'] ?? null)
                && is_numeric($rate['price'] ?? null))
            ->map(fn (array $rate): array => [
                'courier_company' => $rate['courier_company'],
                'courier_type' => $rate['courier_type'],
                'courier_name' => (string) ($rate['courier_name'] ?? strtoupper($rate['courier_company'])),
                'price' => (int) round((float) $rate['price']),
                'duration' => (string) ($rate['duration'] ?? ''),
            ])
            ->values()
            ->all();
    }

    public function createShipment(Order $order): Order
    {
        $this->ensureConfigured();

        if ($order->payment_status !== 'paid') {
            throw new RuntimeException('Pengiriman hanya dapat dibuat setelah pembayaran terkonfirmasi.');
        }

        if ($order->shipping_order_id) {
            return $order;
        }

        $order->loadMissing('orderItems.watch');

        $items = $order->orderItems->map(static fn ($item): array => [
            'name' => $item->watch->name,
            'description' => $item->watch->name,
            'value' => (int) round((float) $item->price_per_item),
            'quantity' => (int) $item->quantity,
            'weight' => (int) $item->watch->weight_gram,
        ])->all();

        $response = $this->client()
            ->post('https://api.biteship.com/v1/orders', [
                'reference_id' => $order->payment_reference ?: 'ORDER-'.$order->id,
                'shipper_contact_name' => config('services.biteship.origin_contact_name'),
                'shipper_contact_phone' => config('services.biteship.origin_contact_phone'),
                'shipper_contact_email' => config('services.biteship.origin_contact_email'),
                'shipper_organization' => config('services.biteship.origin_organization'),
                'origin_contact_name' => config('services.biteship.origin_contact_name'),
                'origin_contact_phone' => config('services.biteship.origin_contact_phone'),
                'origin_address' => config('services.biteship.origin_address'),
                'origin_postal_code' => config('services.biteship.origin_postal_code'),
                'destination_contact_name' => $order->customer_name,
                'destination_contact_phone' => $order->customer_phone,
                'destination_contact_email' => $order->customer_email,
                'destination_address' => $order->shipping_address,
                'destination_postal_code' => $order->shipping_postal_code,
                'courier_company' => $order->shipping_courier,
                'courier_type' => $order->shipping_service,
                'delivery_type' => 'now',
                'items' => $items,
            ])
            ->throw()
            ->json();

        $shippingOrderId = $response['id'] ?? $response['order_id'] ?? null;

        if (! is_string($shippingOrderId) && ! is_numeric($shippingOrderId)) {
            throw new RuntimeException('Biteship tidak mengembalikan ID pengiriman yang valid.');
        }

        $order->forceFill([
            'shipping_order_id' => (string) $shippingOrderId,
            'tracking_number' => $response['waybill_id'] ?? $response['tracking_number'] ?? null,
            'status' => 'processing',
        ])->save();

        return $order->refresh();
    }

    private function ensureConfigured(): void
    {
        $required = [
            'api_key' => config('services.biteship.api_key'),
            'origin_postal_code' => config('services.biteship.origin_postal_code'),
            'origin_contact_name' => config('services.biteship.origin_contact_name'),
            'origin_contact_phone' => config('services.biteship.origin_contact_phone'),
            'origin_contact_email' => config('services.biteship.origin_contact_email'),
            'origin_address' => config('services.biteship.origin_address'),
        ];

        foreach ($required as $name => $value) {
            if (! is_string($value) || trim($value) === '') {
                throw new RuntimeException('Konfigurasi BITESHIP_'.strtoupper($name).' belum lengkap.');
            }
        }
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::acceptJson()
            ->withHeaders(['Authorization' => config('services.biteship.api_key')])
            ->timeout(20);
    }
}
