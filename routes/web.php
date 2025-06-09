<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pos;
use App\Livewire\WatchCatalogPage;
use App\Livewire\Admin\CreateWatch;
// use App\Livewire\WatchRecommendations; // Hapus jika ini bukan halaman penuh
// use App\Livewire\NewsletterSection; // Hapus jika ini bukan halaman penuh

// Route utama (katalog)
// routes/web.php

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pos', Pos::class)->name('pos.index');
Route::get('/katalog', WatchCatalogPage::class)->name('watch-catalog-page.index');



// Jika Anda punya route untuk komponen yang tidak dimaksudkan sebagai halaman penuh, hapus atau sesuaikan.
// Contoh:
// Route::get('/rekomendasi', WatchRecommendations::class); // Jika ini ada, maka WatchRecommendations perlu dikoreksi.