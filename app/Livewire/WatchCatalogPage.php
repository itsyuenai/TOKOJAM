<?php

namespace App\Livewire;

use App\Exceptions\InsufficientStockException;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Watch;
use App\Models\WatchCategory;
use App\Services\CheckoutService;
use Throwable;

class WatchCatalogPage extends Component
{
    use WithPagination;

    // Properti untuk filter dan pencarian
    public string $search = '';
    public string $category = '';
    public string $priceRange = '';
    public string $sortBy = 'name-asc';
    
    // Properti untuk cart functionality
    public array $cart = [];
    public ?string $customerName = null;
    public float $totalAmount = 0;
    public bool $showCart = false;

    // Aturan validasi
    protected $rules = [
        'cart.*.quantity' => 'required|integer|min:1',
        'customerName' => 'nullable|string|max:255',
    ];

    // Query string binding
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'priceRange' => ['except' => ''],
        'sortBy' => ['except' => 'name-asc'],
    ];

    public function mount()
    {
        $this->loadCartFromSession();
        $this->updateTotalAmount();
    }

    // Load cart from session
    private function loadCartFromSession()
    {
        $this->cart = session()->get('cart', []);
    }

    // Save cart to session
    private function saveCartToSession()
    {
        session()->put('cart', $this->cart);
    }

    // Reset pagination ketika filter berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingPriceRange()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    // Fungsi untuk menambah item ke cart
    public function addToCart(int $watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Produk tidak ditemukan!'
            ]);
            return;
        }

        if ($watch->stock <= 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Stok ' . $watch->name . ' habis!'
            ]);
            return;
        }

        $cartKey = (string)$watchId;
        
        if (isset($this->cart[$cartKey])) {
            if ($this->cart[$cartKey]['quantity'] + 1 > $watch->stock) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Stok ' . $watch->name . ' tidak mencukupi!'
                ]);
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'id' => $watch->id,
                'name' => $watch->name,
                'price' => (float)$watch->price,
                'quantity' => 1,
                'sku' => $watch->sku,
                'current_stock' => $watch->stock,
                'image' => $watch->image,
            ];
        }

        $this->saveCartToSession();
        $this->updateTotalAmount();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $watch->name . ' ditambahkan ke keranjang!'
        ]);
    }

    // Fungsi untuk menghapus item dari cart
    public function removeFromCart(int $watchId)
    {
        $cartKey = (string)$watchId;
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->saveCartToSession();
            $this->updateTotalAmount();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Produk dihapus dari keranjang!'
            ]);
        }
    }

    // Update quantity di cart
    public function updateCartQuantity(int $watchId, int $quantity)
    {
        $cartKey = (string)$watchId;
        if (isset($this->cart[$cartKey])) {
            $watch = Watch::find($watchId);
            if (!$watch) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Produk tidak ditemukan!'
                ]);
                $this->removeFromCart($watchId);
                return;
            }

            if ($quantity <= 0) {
                $this->removeFromCart($watchId);
                return;
            }

            if ($quantity > $watch->stock) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Kuantitas ' . $watch->name . ' melebihi stok yang tersedia (' . $watch->stock . ')!'
                ]);
                $this->cart[$cartKey]['quantity'] = $watch->stock;
                $this->saveCartToSession();
                $this->updateTotalAmount();
                return;
            }

            $this->cart[$cartKey]['quantity'] = $quantity;
            $this->saveCartToSession();
            $this->updateTotalAmount();
        }
    }

    // Update total amount
    public function updateTotalAmount()
    {
        $this->totalAmount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $this->cart));
    }

    // Toggle cart visibility
    public function toggleCart()
    {
        $this->showCart = !$this->showCart;
    }

    // Clear cart
    public function clearCart()
    {
        $this->cart = [];
        $this->saveCartToSession();
        $this->updateTotalAmount();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Keranjang dikosongkan!'
        ]);
    }

    // Checkout function
    public function checkout()
    {
        $this->validate();

        if (empty($this->cart)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Keranjang belanja kosong!'
            ]);
            return;
        }

        try {
            $order = app(CheckoutService::class)->process(
                items: $this->checkoutItems(),
                customerName: $this->customerName ?: 'Walk-in Customer',
            );

            // Reset cart setelah berhasil
            $this->cart = [];
            $this->customerName = null;
            $this->showCart = false;
            $this->saveCartToSession();
            $this->updateTotalAmount();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil! Order #' . $order->id . ' telah selesai.'
            ]);

            // Redirect ke halaman success atau print receipt
            $this->dispatch('orderCompleted', ['orderId' => $order->id]);

        } catch (InsufficientStockException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage() . ' Tersedia: ' . $e->availableStock . '.'
            ]);
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat checkout. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function checkoutItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ],
            array_values($this->cart),
        );
    }

    // Get cart item count
    public function getCartItemCountProperty()
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function render()
    {
        // Ambil semua kategori
        $categories = WatchCategory::orderBy('name')->get();

        // Query watches dengan filter
        $watches = Watch::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category, function ($query) {
                $query->where('category_id', $this->category);
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
            ->with('category');

        // Apply sorting
        switch ($this->sortBy) {
            case 'name-desc':
                $watches->orderBy('name', 'desc');
                break;
            case 'price-asc':
                $watches->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $watches->orderBy('price', 'desc');
                break;
            default:
                $watches->orderBy('name', 'asc');
        }

        $watches = $watches->paginate(12);

        return view('livewire.watch-catalog-page', [
            'watches' => $watches,
            'categories' => $categories,
        ]);
    }
}
