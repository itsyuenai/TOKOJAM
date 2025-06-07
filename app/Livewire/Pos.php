<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Watch; // Menggunakan model Watch
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BarangKeluar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification; // Untuk notifikasi

class Pos extends Component
{
    public string $search = ''; // Properti untuk pencarian produk
    public Collection $products; // Koleksi jam tangan yang ditemukan dari pencarian
    public array $cart = []; // Array keranjang belanja
    public ?string $customerName = null; // Nama pelanggan (opsional)
    public float $totalAmount = 0; // Total jumlah pesanan

    // Aturan validasi untuk item di keranjang
    protected $rules = [
        'cart.*.quantity' => 'required|integer|min:1',
        'customerName' => 'nullable|string|max:255',
    ];

    // Listeners untuk merefresh keranjang jika ada perubahan stok dari admin panel (opsional, untuk real-time sync)
    // protected $listeners = ['watchStockUpdated' => 'refreshCart']; // Contoh

    // Metode mount dipanggil saat komponen diinisialisasi
    public function mount()
    {
        $this->products = collect(); // Inisialisasi koleksi produk kosong
        $this->updateTotalAmount(); // Hitung ulang total saat mount
    }

    // Metode ini dipanggil setiap kali properti $search diperbarui
    public function updatedSearch()
    {
        if (strlen($this->search) > 2) { // Minimal 3 karakter untuk memicu pencarian
            $this->products = Watch::where('name', 'ilike', '%' . $this->search . '%') // Pencarian case-insensitive
                                ->orWhere('sku', 'ilike', '%' . $this->search . '%')
                                ->where('stock', '>', 0) // Hanya tampilkan yang stoknya tersedia
                                ->get();
        } else {
            $this->products = collect(); // Kosongkan produk jika pencarian terlalu pendek
        }
    }

    // Metode untuk menambahkan produk ke keranjang
    public function addToCart(int $watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            Notification::make()
                ->title('Produk tidak ditemukan!')
                ->danger()
                ->send();
            return;
        }

        if ($watch->stock <= 0) {
            Notification::make()
                ->title('Stok ' . $watch->name . ' habis!')
                ->danger()
                ->send();
            return;
        }

        if (isset($this->cart[$watchId])) {
            // Jika produk sudah ada di keranjang, tambahkan kuantitas
            if ($this->cart[$watchId]['quantity'] + 1 > $watch->stock) {
                Notification::make()
                    ->title('Stok ' . $watch->name . ' tidak mencukupi!')
                    ->danger()
                    ->send();
                return;
            }
            $this->cart[$watchId]['quantity']++;
        } else {
            // Jika produk belum ada di keranjang, tambahkan item baru
            $this->cart[$watchId] = [
                'id' => $watch->id,
                'name' => $watch->name,
                'price' => (float)$watch->price,
                'quantity' => 1,
                'sku' => $watch->sku,
                'current_stock' => $watch->stock, // Simpan stok saat ini untuk validasi
            ];
        }
        $this->updateTotalAmount();
        Notification::make()
            ->title('Produk ditambahkan ke keranjang!')
            ->success()
            ->send();
    }

    // Metode untuk menghapus item dari keranjang
    public function removeFromCart(int $watchId)
    {
        if (isset($this->cart[$watchId])) {
            unset($this->cart[$watchId]);
            $this->updateTotalAmount();
            Notification::make()
                ->title('Produk dihapus dari keranjang!')
                ->success()
                ->send();
        }
    }

    // Metode untuk memperbarui kuantitas item di keranjang
    public function updateCartQuantity(int $watchId, int $quantity)
    {
        if (isset($this->cart[$watchId])) {
            $watch = Watch::find($watchId); // Ambil data jam tangan terbaru
            if (!$watch) {
                Notification::make()
                    ->title('Produk tidak ditemukan!')
                    ->danger()
                    ->send();
                $this->removeFromCart($watchId); // Hapus dari keranjang jika tidak ada
                return;
            }

            if ($quantity <= 0) {
                $this->removeFromCart($watchId);
                return;
            }

            if ($quantity > $watch->stock) {
                Notification::make()
                    ->title('Kuantitas ' . $watch->name . ' melebihi stok yang tersedia (' . $watch->stock . ')!')
                    ->danger()
                    ->send();
                $this->cart[$watchId]['quantity'] = $watch->stock; // Set kuantitas ke stok maksimal
                $this->updateTotalAmount();
                return;
            }

            $this->cart[$watchId]['quantity'] = $quantity;
            $this->updateTotalAmount();
        }
    }

    // Metode untuk menghitung ulang total harga keranjang
    public function updateTotalAmount()
    {
        $this->totalAmount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $this->cart));
    }

    // Metode untuk memproses checkout
    public function checkout()
    {
        $this->validate(); // Validasi properti keranjang berdasarkan $rules

        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang belanja kosong! Tidak ada yang bisa di-checkout.')
                ->danger()
                ->send();
            return;
        }

        DB::beginTransaction(); // Memulai transaksi database
        try {
            // Buat Order baru
            $order = Order::create([
                'customer_name' => $this->customerName,
                'total_amount' => $this->totalAmount,
                'status' => 'completed', // Langsung selesai setelah checkout
            ]);

            foreach ($this->cart as $item) {
                // Buat OrderItem
                OrderItem::create([
                    'order_id' => $order->id,
                    'jam_id' => $item['id'], // 'jam_id' karena kolom foreign key di migrasi dan model masih bernama jam_id
                    'quantity' => $item['quantity'],
                    'price_per_item' => $item['price'],
                ]);

                // Update stok jam tangan
                $watch = Watch::find($item['id']);
                if ($watch) {
                    if ($watch->stock < $item['quantity']) {
                         // Ini seharusnya sudah divalidasi oleh updateCartQuantity, tapi sebagai fail-safe
                         DB::rollBack();
                         Notification::make()
                             ->title('Stok ' . $watch->name . ' tidak mencukupi saat checkout!')
                             ->danger()
                             ->send();
                         return;
                    }
                    $watch->decrement('stock', $item['quantity']);

                    // Catat sebagai barang keluar (terkait penjualan)
                    BarangKeluar::create([
                        'jam_id' => $watch->id, // 'jam_id' karena kolom foreign key di migrasi dan model masih bernama jam_id
                        'quantity' => $item['quantity'],
                        'customer_name' => $this->customerName,
                        'sale_price' => $item['price'],
                        'exit_date' => now(),
                    ]);
                }
            }

            DB::commit(); // Commit transaksi jika semua berhasil

            // Reset keranjang dan form setelah checkout berhasil
            $this->cart = [];
            $this->search = '';
            $this->products = collect();
            $this->customerName = null;
            $this->updateTotalAmount(); // Update total kembali ke 0

            Notification::make()
                ->title('Pesanan berhasil dibuat! Stok telah diperbarui.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika ada error
            Notification::make()
                ->title('Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage())
                ->danger()
                ->send();
            // Anda bisa log error $e->getMessage() di sini untuk debugging
        }
    }

    // Metode render untuk menampilkan tampilan komponen
    public function render()
    {
        return view('livewire.pos', [
            'jamsInCart' => collect($this->cart), // Lewatkan item keranjang ke view
        ]);
    }
}