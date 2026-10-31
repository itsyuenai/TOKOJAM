<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class MidtransService
{
    /**
     * @return array{token: string, redirect_url: string}
     */
    public function createPayment(Order $order): array
    {
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diatur.');
        }

        $baseUrl = config('services.midtrans.sandbox')
            ? 'https://app.sandbox.midtrans.com'
            : 'https://app.midtrans.com';

        $items = $order->orderItems()->with('watch')->get()->map(static fn ($item): array => [
            'id' => (string) $item->jam_id,
            'price' => (int) round((float) $item->price_per_item),
            'quantity' => (int) $item->quantity,
            'name' => Str::limit($item->watch->name, 50, ''),
        ])->all();

        if ((float) $order->shipping_fee > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) round((float) $order->shipping_fee),
                'quantity' => 1,
                'name' => 'Ongkos kirim',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->payment_reference,
                'gross_amount' => (int) round((float) $order->total_amount),
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'shipping_address' => [
                    'first_name' => $order->customer_name,
                    'phone' => $order->customer_phone,
                    'address' => $order->shipping_address,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
            ],
            'callbacks' => [
                'finish' => route('orders.track', $order->tracking_token),
            ],
        ];

        $response = Http::acceptJson()
            ->withBasicAuth($serverKey, '')
            ->timeout(20)
            ->post($baseUrl.'/snap/v1/transactions', $payload)
            ->throw()
            ->json();

        if (! is_array($response)
            || ! is_string($response['token'] ?? null)
            || ! is_string($response['redirect_url'] ?? null)) {
            throw new RuntimeException('Midtrans tidak mengembalikan token pembayaran yang valid.');
        }

        return [
            'token' => $response['token'],
            'redirect_url' => $response['redirect_url'],
        ];
    }

    /**
     * Verify notification authenticity before using any payment status in its payload.
     *
     * @param  array<string, mixed>  $notification
     */
    public function verifyNotification(array $notification): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];

        if (! is_string($serverKey) || $serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diatur.');
        }

        foreach ($required as $field) {
            if (! is_string($notification[$field] ?? null)) {
                return false;
            }
        }

        $expected = hash('sha512', implode('', [
            $notification['order_id'],
            $notification['status_code'],
            $notification['gross_amount'],
            $serverKey,
        ]));

        return hash_equals($expected, $notification['signature_key']);
    }
}
