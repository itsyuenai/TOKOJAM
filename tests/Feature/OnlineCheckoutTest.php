<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Order;
use App\Models\User;
use App\Models\Watch;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnlineCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_catalog_route_uses_the_online_storefront(): void
    {
        $this->get('/katalog')->assertRedirect('/');
    }

    public function test_demo_catalog_shows_three_local_product_photos_and_an_embedded_map(): void
    {
        $this->seed(\Database\Seeders\DemoWatchCatalogSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://www.openstreetmap.org/export/embed.html', false)
            ->assertSee('smartwatch-minimal.jpg')
            ->assertSee('classic-leather-watch.jpg')
            ->assertSee('chronograph-heritage.jpg')
            ->assertSee('Toko Jam Citeureup')
            ->assertSee('Citeureup, Kabupaten Bogor, Jawa Barat 16810')
            ->assertSee('106.8810')
            ->assertSee('Checkout barang dipilih')
            ->assertSee('Cek ongkir kurir')
            ->assertSee('x-show="csrfMismatch"', false)
            ->assertSee('x-model="item.selected"', false);

        $this->assertDatabaseCount('watches', 3);
    }

    public function test_pos_is_reserved_for_administrators(): void
    {
        $this->get('/pos')->assertRedirect(route('filament.admin.auth.login'));

        $this->actingAs(User::factory()->create())
            ->get('/pos')
            ->assertForbidden();
    }

    public function test_admin_creation_command_grants_admin_access_to_an_existing_user(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:make-admin', ['email' => $user->email])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_customer_can_get_shipping_rates_and_create_a_pending_online_order(): void
    {
        $this->configureProviders();
        $watch = $this->createWatch();

        $rates = $this->postJson(route('shipping.rates'), [
            'destination_postal_code' => '10110',
            'items' => [['product_id' => $watch->id, 'quantity' => 1]],
        ]);

        $rates->assertOk()
            ->assertJsonPath('rates.0.courier_company', 'jne')
            ->assertJsonPath('rates.0.price', 50000);

        $checkout = $this->postJson(route('checkout.process'), [
            'customer_name' => 'Pelanggan Uji',
            'customer_email' => 'buyer@example.test',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jl. Contoh No. 1, Jakarta',
            'shipping_postal_code' => '10110',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'items' => [['product_id' => $watch->id, 'quantity' => 1]],
        ]);

        $checkout->assertCreated()
            ->assertJsonPath('order.status', 'pending')
            ->assertJsonPath('order.payment_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/test-token');

        $order = Order::query()->firstOrFail();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('1050000.00', $order->total_amount);
        $this->assertSame(1, $watch->fresh()->stock);

        $this->get(route('orders.track', $order->tracking_token))->assertOk();
    }

    public function test_shipping_quote_rejects_excessive_duplicate_product_quantities(): void
    {
        $this->configureProviders();
        $watch = $this->createWatch();

        $this->postJson(route('shipping.rates'), [
            'destination_postal_code' => '10110',
            'items' => [
                ['product_id' => $watch->id, 'quantity' => 20],
                ['product_id' => $watch->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    }

    public function test_pos_checkout_remains_paid_and_is_recorded_in_the_sales_ledger(): void
    {
        $watch = $this->createWatch();

        $order = app(CheckoutService::class)->process(
            items: [['product_id' => $watch->id, 'quantity' => 1]],
            customerName: 'Walk-in Customer',
        );

        $this->assertSame('completed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(1, $watch->fresh()->stock);
        $this->assertDatabaseHas('barang_keluar', [
            'order_id' => $order->id,
            'jam_id' => $watch->id,
            'customer_name' => 'Walk-in Customer',
        ]);
    }

    public function test_admin_inventory_movements_update_stock_and_reject_overselling(): void
    {
        $watch = $this->createWatch();

        $incoming = DB::transaction(fn (): BarangMasuk => BarangMasuk::query()->create([
            'jam_id' => $watch->id,
            'quantity' => 3,
            'purchase_price' => 700000,
            'entry_date' => now()->toDateString(),
        ]));
        $this->assertSame(5, $watch->fresh()->stock);

        DB::transaction(function () use ($incoming): void {
            $incoming->update(['quantity' => 4]);
        });
        $this->assertSame(6, $watch->fresh()->stock);

        $outgoing = DB::transaction(fn (): BarangKeluar => BarangKeluar::query()->create([
            'jam_id' => $watch->id,
            'quantity' => 2,
            'sale_price' => 1000000,
            'exit_date' => now()->toDateString(),
        ]));
        $this->assertSame(4, $watch->fresh()->stock);

        DB::transaction(function () use ($outgoing): void {
            $outgoing->update(['quantity' => 1]);
        });
        $this->assertSame(5, $watch->fresh()->stock);

        try {
            DB::transaction(fn () => BarangKeluar::query()->create([
                'jam_id' => $watch->id,
                'quantity' => 6,
                'sale_price' => 1000000,
                'exit_date' => now()->toDateString(),
            ]));
            $this->fail('Stok tidak cukup seharusnya menolak transaksi barang keluar.');
        } catch (InsufficientStockException) {
            $this->assertSame(5, $watch->fresh()->stock);
        }

        DB::transaction(fn () => $outgoing->delete());
        DB::transaction(fn () => $incoming->delete());
        $this->assertSame(2, $watch->fresh()->stock);
    }

    public function test_only_a_valid_midtrans_notification_confirms_payment_and_books_shipping(): void
    {
        $this->configureProviders();
        $watch = $this->createWatch();
        $checkout = $this->postJson(route('checkout.process'), [
            'customer_name' => 'Pelanggan Uji',
            'customer_email' => 'buyer@example.test',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jl. Contoh No. 1, Jakarta',
            'shipping_postal_code' => '10110',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'items' => [['product_id' => $watch->id, 'quantity' => 1]],
        ])->assertCreated();

        $order = Order::query()->firstOrFail();
        $notification = [
            'order_id' => $order->payment_reference,
            'status_code' => '200',
            'gross_amount' => '1050000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        $this->postJson(route('payments.midtrans.notification'), $notification + [
            'signature_key' => 'invalid',
        ])->assertUnauthorized();
        $this->assertSame('pending', $order->fresh()->payment_status);

        $notification['signature_key'] = hash('sha512', implode('', [
            $notification['order_id'],
            $notification['status_code'],
            $notification['gross_amount'],
            'sandbox-server-key',
        ]));

        $this->postJson(route('payments.midtrans.notification'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('biteship-order-1', $order->fresh()->shipping_order_id);
        $this->assertSame('JNE123456', $order->fresh()->tracking_number);
        $this->assertSame(1, $watch->fresh()->stock);
        $this->assertDatabaseHas('barang_keluar', [
            'order_id' => $order->id,
            'jam_id' => $watch->id,
            'quantity' => 1,
        ]);

        $this->postJson(route('payments.midtrans.notification'), $notification)->assertOk();
        $this->assertDatabaseCount('barang_keluar', 1);
        Http::assertSentCount(3);
    }

    public function test_expired_payment_releases_reserved_stock_once(): void
    {
        $this->configureProviders();
        $watch = $this->createWatch();
        $this->postJson(route('checkout.process'), [
            'customer_name' => 'Pelanggan Uji',
            'customer_email' => 'buyer@example.test',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jl. Contoh No. 1, Jakarta',
            'shipping_postal_code' => '10110',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'items' => [['product_id' => $watch->id, 'quantity' => 1]],
        ])->assertCreated();

        $order = Order::query()->firstOrFail();
        $notification = [
            'order_id' => $order->payment_reference,
            'status_code' => '200',
            'gross_amount' => '1050000.00',
            'transaction_status' => 'expire',
        ];
        $notification['signature_key'] = hash('sha512', implode('', [
            $notification['order_id'],
            $notification['status_code'],
            $notification['gross_amount'],
            'sandbox-server-key',
        ]));

        $this->postJson(route('payments.midtrans.notification'), $notification)->assertOk();
        $this->postJson(route('payments.midtrans.notification'), $notification)->assertOk();

        $this->assertSame('expire', $order->fresh()->payment_status);
        $this->assertTrue($order->fresh()->stock_released);
        $this->assertSame(2, $watch->fresh()->stock);
        $this->assertDatabaseCount('barang_keluar', 0);
    }

    private function configureProviders(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.client_key' => 'sandbox-client-key',
            'services.midtrans.sandbox' => true,
            'services.biteship.api_key' => 'sandbox-biteship-key',
            'services.biteship.origin_postal_code' => '10220',
            'services.biteship.origin_contact_name' => 'Toko Jam',
            'services.biteship.origin_contact_phone' => '081234567890',
            'services.biteship.origin_contact_email' => 'store@example.test',
            'services.biteship.origin_address' => 'Jl. Asal No. 1, Jakarta',
            'services.biteship.origin_organization' => 'Toko Jam',
            'services.biteship.couriers' => 'jne,jnt',
        ]);

        Http::fake([
            'api.biteship.com/v1/rates/couriers' => Http::response([
                'success' => true,
                'pricing' => [[
                    'courier_company' => 'jne',
                    'courier_type' => 'reg',
                    'courier_name' => 'JNE',
                    'price' => 50000,
                    'duration' => '1-2 days',
                ]],
            ]),
            'api.biteship.com/v1/orders' => Http::response([
                'id' => 'biteship-order-1',
                'waybill_id' => 'JNE123456',
            ]),
            'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'test-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/test-token',
            ]),
        ]);
    }

    private function createWatch(): Watch
    {
        return Watch::query()->create([
            'name' => 'Jam Uji',
            'price' => 1000000,
            'stock' => 2,
            'weight_gram' => 500,
            'sku' => 'TEST-WATCH',
        ]);
    }
}
