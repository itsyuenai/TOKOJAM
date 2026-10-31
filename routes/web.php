<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ShippingRateController;
use App\Livewire\Pos;
use App\Livewire\Admin\CreateWatch;
use App\Models\Watch;
use Illuminate\Support\Str;
// use App\Livewire\WatchRecommendations; // Hapus jika ini bukan halaman penuh
// use App\Livewire\NewsletterSection; // Hapus jika ini bukan halaman penuh

// Route utama (katalog)
// routes/web.php

Route::get('/', function () {
    $fallbackImages = [
        'Rolex Submariner' => 'https://images.voila.id/pr:sharp/rs:fit:2048/plain/https%3A%2F%2Fassets.voila.id%2Fvoila%2Fimages%2Fproduct%2Frolex%2F2product-126610LN-Xms-2022-05-30T1259420700.jpeg@webp',
        'Casio G-Shock GA-700' => 'https://www.jamcasio.com/image/cache/catalog/product/G-SHOCK-GA-700-1BDR-500x500.jpg',
        'Tag Heuer Formula 1' => 'https://www.tagheuer.com/on/demandware.static/-/Sites-tagheuer-master/default/dwd6327dc6/TAG_Heuer_Formula_1/WAZ1110.FT8023/WAZ1110.FT8023_1000.png',
        'Orient Envoy Open Heart' => 'https://dynamic.zacdn.com/PKFfsuhHOjVFpcfyOGuD9wRC73k=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/orient-4255-4136864-1.jpg',
        'Digitec Nexus' => 'https://dynamic.zacdn.com/SbEiEzmSmM15ubqYPCtAcGauGdI=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/digitec-6568-2226494-1.jpg',
        'Seiko Prospex Speedtimer' => 'https://www.nzwatches.com/media/catalog/product/cache/f20a3dad9a18e1857a6578422466bfb5/s/s/ssc813p1_00.jpg',
        'Tissot Gentleman Powermatic 80' => 'https://wornandwound.com/library/uploads/2020/02/Tissot-Gentleman-Powermatic-80-Silicium-6-scaled.jpg',
        'Vintage Omega Seamaster' => 'https://static.wixstatic.com/media/a954ba_0f2d2ee1265541bdb4bb65317a3cf18e~mv2.jpeg/v1/fill/w_514,h_514,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/a954ba_0f2d2ee1265541bdb4bb65317a3cf18e~mv2.jpeg',
    ];

    try {
        $watches = Watch::query()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function (Watch $watch) use ($fallbackImages): array {
                $categoryName = $watch->category?->name ?? 'Uncategorized';
                $image = $watch->image;

                if ($image && ! Str::startsWith($image, ['http://', 'https://'])) {
                    $image = Str::startsWith($image, 'images/')
                        ? asset($image)
                        : asset('storage/'.$image);
                }

                return [
                    'id' => $watch->id,
                    'name' => $watch->name,
                    'category' => Str::slug($categoryName),
                    'categoryName' => $categoryName,
                    'price' => (float) $watch->price,
                    'stock' => (int) $watch->stock,
                    'description' => $watch->description,
                    'sku' => $watch->sku,
                    'image' => $image ?: ($fallbackImages[$watch->name] ?? null),
                ];
            })
            ->values();
    } catch (Throwable $exception) {
        report($exception);

        $watches = collect();
    }

    return view('welcome', [
        'watches' => $watches,
    ]);
});

Route::get('/pos', Pos::class)->middleware('admin')->name('pos.index');
Route::redirect('/katalog', '/')->name('watch-catalog-page.index');
Route::post('/checkout', CheckoutController::class)->middleware('throttle:5,1')->name('checkout.process');
Route::post('/shipping/rates', ShippingRateController::class)->middleware('throttle:10,1')->name('shipping.rates');
Route::post('/payments/midtrans/notification', MidtransNotificationController::class)
    ->name('payments.midtrans.notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::get('/pesanan/{token}', OrderTrackingController::class)->name('orders.track');
Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
Route::post('/helpdesk/chat', HelpdeskController::class)
    ->middleware('throttle:10,1')
    ->name('helpdesk.chat');



// Jika Anda punya route untuk komponen yang tidak dimaksudkan sebagai halaman penuh, hapus atau sesuaikan.
// Contoh:
// Route::get('/rekomendasi', WatchRecommendations::class); // Jika ini ada, maka WatchRecommendations perlu dikoreksi.
