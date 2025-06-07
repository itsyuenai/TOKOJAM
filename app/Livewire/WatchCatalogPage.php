<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Watch;
use App\Models\Category;

class WatchCatalogPage extends Component
{
    use WithPagination;

    // Properti untuk filter pencarian (dari header dan hero section)
    public string $search = '';

    // Properti untuk filter dari komponen WatchFilters (akan di-update melalui event)
    public string $category = '';
    public string $priceRange = '';

    // Mengikat properti publik ke parameter URL
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'priceRange' => ['except' => ''],
    ];

    // Listeners untuk event dari komponen WatchFilters
    // Nama event harus cocok dengan yang dipancarkan oleh WatchFilters
    protected $listeners = [
        'filtersUpdated' => 'applyFilters', // Ketika WatchFilters memancarkan 'filtersUpdated'
    ];

    // Metode yang dipanggil ketika properti $search diupdate
    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination saat pencarian berubah
    }

    // Metode untuk menerima dan menerapkan filter dari WatchFilters
    public function applyFilters(string $categorySlug, string $priceRangeValue)
    {
        $this->category = $categorySlug;
        $this->priceRange = $priceRangeValue;
        $this->resetPage(); // Reset pagination saat filter berubah
    }

    // Placeholder untuk logika penambahan produk ke keranjang
    public function addToCart($watchId)
    {
        // Implementasi logika menambahkan jam ke keranjang.
        // Ini bisa berupa dispatching event ke komponen keranjang lain,
        // menyimpan ke session, atau berinteraksi dengan database.
        \Filament\Notifications\Notification::make()
            ->title('Produk berhasil ditambahkan ke keranjang (simulasi)!')
            ->success()
            ->send();
    }

    // Placeholder untuk logika pembelian langsung
    public function buyNow($watchId)
    {
        // Implementasi logika pembelian langsung.
        // Ini mungkin akan mengarahkan pengguna ke halaman checkout dengan produk ini.
        \Filament\Notifications\Notification::make()
            ->title('Proses pembelian langsung (simulasi)!')
            ->info()
            ->send();
    }

    public function render()
    {
        // Mengambil semua kategori untuk navigasi di bagian tengah (seperti yang Anda inginkan)
        $categoriesForNav = Category::orderBy('name')->get();

        // Query untuk mengambil jam tangan berdasarkan filter
        $watches = Watch::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('description', 'ilike', '%' . $this->search . '%')
                      ->orWhere('sku', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->category, function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('slug', $this->category);
                });
            })
            ->when($this->priceRange, function ($query) {
                switch ($this->priceRange) {
                    case 'under-2m':
                        $query->where('price', '<', 2000000);
                        break;
                    case '2m-5m':
                        $query->whereBetween('price', [2000000, 5000000]);
                        break;
                    case 'above-5m':
                        $query->where('price', '>', 5000000);
                        break;
                }
            })
            ->where('stock', '>', 0) // Hanya tampilkan jam yang memiliki stok
            ->with('category') // Eager load relasi kategori
            ->orderBy('name')
            ->paginate(9); // Pagination

        return view('livewire.watch-catalog-page', [
            'watches' => $watches,
            'categories' => $categoriesForNav, // Menggunakan nama variabel yang berbeda agar tidak bingung dengan filter
        ]);
    }
}