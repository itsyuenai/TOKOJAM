<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Watch - Luxury Timepieces Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-bg {
            background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)), 
                        url('https://img.freepik.com/free-photo/futuristic-time-machine_23-2151599396.jpg?semt=ais_hybrid&w=740');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .watch-card {
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .watch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .price-tag {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            font-weight: bold;
        }
        .cart-sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .cart-sidebar.open {
            transform: translateX(0);
        }
        .cart-sidebar.closed {
            transform: translateX(100%);
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transform: translateX(400px);
            transition: transform 0.3s ease-in-out;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.success {
            background-color: #10b981;
        }
        .notification.error {
            background-color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Header -->
<header class="bg-white shadow-lg sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <h1 class="text-3xl font-bold text-gray-900">ID Watch</h1>
            </div>
            
            <!-- Search Bar -->
            <div class="flex-1 max-w-lg mx-8">
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="text" 
                    placeholder="Cari jam tangan..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>
            
            <!-- Cart Button -->
            <div class="flex items-center space-x-4">
                <button 
                    wire:click="toggleCart"
                    class="relative bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300"
                >
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Keranjang
                    @if($cartItemCount > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">
                            {{ $cartItemCount }}
                        </span>
                    @endif
                </button>
                <a href="{{ route('pos') }}" class="text-orange-600 hover:text-orange-800 font-medium">POS System</a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero-bg h-96 flex items-center justify-center text-white">
    <div class="text-center">
        <h2 class="text-5xl font-bold mb-4">
            Discover Timeless
            <span class="text-orange-400">Luxury</span> 
        </h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Jelajahi koleksi eksklusif jam tangan premium merk terkemuka dunia
        </p>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Filters & Sorting -->
    <div class="flex flex-wrap gap-4 mb-8 items-center justify-between bg-white p-4 rounded-lg shadow">
        <div class="flex flex-wrap gap-4">
            <!-- Category Filter -->
            <select wire:model.live="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            
            <!-- Price Range Filter -->
            <select wire:model.live="priceRange" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                <option value="">Semua Harga</option>
                <option value="under-2m">Di bawah Rp 2.000.000</option>
                <option value="2m-5m">Rp 2.000.000 - Rp 5.000.000</option>
                <option value="above-5m">Di atas Rp 5.000.000</option>
            </select>
        </div>
        
        <!-- Sort -->
        <div class="flex items-center gap-4">
            <label class="text-gray-700 font-medium">Urutkan:</label>
            <select wire:model.live="sortBy" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                <option value="name-asc">Nama A-Z</option>
                <option value="name-desc">Nama Z-A</option>
                <option value="price-asc">Harga: Terendah ke Tertinggi</option>
                <option value="price-desc">Harga: Tertinggi ke Terendah</option>
            </select>
        </div>
    </div>

    <!-- Results Count -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-900">Koleksi Premium</h3>
        <p class="text-gray-600">{{ $watches->total() }} jam ditemukan</p>
    </div>

    <!-- Watch Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        @forelse($watches as $watch)
            <div class="watch-card rounded-xl overflow-hidden">
                <div class="relative">
                    @if($watch->image)
                        <img src="{{ $watch->image }}" alt="{{ $watch->name }}" class="w-full h-64 object-cover">
                    @else
                        <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-clock text-4xl text-gray-400"></i>
                        </div>
                    @endif
                    
                    @if($watch->category)
                        <div class="absolute top-4 left-4">
                            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-xs px-2 py-1 rounded-full">
                                {{ $watch->category->name }}
                            </span>
                        </div>
                    @endif
                </div>
                
                <div class="p-6">
                    <h4 class="font-bold text-lg text-gray-900 mb-2">{{ $watch->name }}</h4>
                    
                    @if($watch->description)
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ Str::limit($watch->description, 80) }}</p>
                    @endif
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="price-tag px-3 py-1 rounded-lg text-lg font-bold">
                            Rp {{ number_format($watch->price, 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-gray-600">
                            Stok: {{ $watch->stock }}
                        </div>
                    </div>
                    
                    <button 
                        wire:click="addToCart({{ $watch->id }})"
                        @if($watch->stock <= 0) disabled @endif
                        class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white py-2 px-4 rounded-lg font-semibold transition duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        @if($watch->stock <= 0)
                            Stok Habis
                        @else
                            <i class="fas fa-cart-plus mr-2"></i>
                            Tambah ke Keranjang
                        @endif
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-search text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada produk ditemukan</h3>
                <p class="text-gray-500">Coba ubah filter pencarian Anda</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $watches->links() }}
    </div>
</div>

<!-- Cart Sidebar -->
<div class="fixed inset-0 z-50 overflow-hidden {{ $showCart ? '' : 'pointer-events-none' }}" 
     style="display: {{ $showCart ? 'block' : 'none' }}">
    
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50 {{ $showCart ? 'opacity-100' : 'opacity-0' }}" 
         wire:click="toggleCart"></div>
    
    <!-- Sidebar -->
    <div class="cart-sidebar {{ $showCart ? 'open' : 'closed' }} absolute right-0 top-0 h-full w-96 bg-white shadow-xl flex flex-col">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b">
            <h2 class="text-xl font-semibold">Keranjang Belanja</h2>
            <div class="flex items-center space-x-2">
                @if(!empty($cart))
                    <button wire:click="clearCart" class="text-red-500 hover:text-red-700 text-sm">
                        <i class="fas fa-trash mr-1"></i>
                        Kosongkan
                    </button>
                @endif
                <button wire:click="toggleCart" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-6">
            @if(empty($cart))
                <div class="text-center py-12">
                    <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Keranjang kosong</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($cart as $item)
                        <div class="flex items-start space-x-3 border-b pb-4">
                            @if(isset($item['image']) && $item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                            @endif
                            
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 text-sm">{{ $item['name'] }}</h4>
                                <p class="text-sm text-gray-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                <div class="flex items-center space-x-2 mt-2">
                                    <button 
                                        wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                        class="w-6 h-6 bg-gray-200 rounded flex items-center justify-center text-sm hover:bg-gray-300"
                                    >
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <span class="px-2 text-sm">{{ $item['quantity'] }}</span>
                                    <button 
                                        wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                        class="w-6 h-6 bg-gray-200 rounded flex items-center justify-center text-sm hover:bg-gray-300"
                                    >
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                    <button 
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        class="text-red-500 hover:text-red-700 ml-2"
                                    >
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        @if(!empty($cart))
            <div class="border-t p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan (Opsional)</label>
                    <input 
                        wire:model="customerName"
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-500"
                        placeholder="Masukkan nama..."
                    >
                </div>
                
                <div class="flex justify-between items-center text-xl font-bold mb-4">
                    <span>Total:</span>
                    <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </div>
                
                <button 
                    wire:click="checkout"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold transition duration-300"
                >
                    <i class="fas fa-credit-card mr-2"></i>
                    Bayar Sekarang
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Notification -->
<div id="notification" class="notification" style="display: none;"></div>

@livewireScripts

{{-- PERBAIKAN: Seluruh blok skrip diganti dengan yang lebih aman --}}
<script>
    document.addEventListener('livewire:init', () => {
        // Sistem Notifikasi
        window.addEventListener('notify', event => {
            // Pemeriksaan untuk memastikan data event valid sebelum digunakan
            if (!event.detail || typeof event.detail.type === 'undefined' || typeof event.detail.message === 'undefined') {
                console.error('Event notifikasi tidak valid diterima:', event);
                return;
            }

            const notification = document.getElementById('notification');
            const { type, message } = event.detail;

            notification.textContent = message;
            notification.className = `notification ${type}`; // Menggunakan kelas success atau error
            notification.style.display = 'block';
            
            // Memicu animasi fade-in
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Menyembunyikan notifikasi setelah 3 detik
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300); // Waktu ini harus cocok dengan durasi transisi CSS
            }, 3000);
        });

        // Handler setelah order selesai (untuk masa depan, misal cetak struk)
        window.addEventListener('orderCompleted', event => {
            if (!event.detail || typeof event.detail.orderId === 'undefined') {
                console.error('Event orderCompleted tidak valid diterima:', event);
                return;
            }
            const { orderId } = event.detail;
            console.log('Order selesai:', orderId);
            // Di sini Anda bisa menambahkan logika redirect atau memanggil fungsi cetak
        });
    });
</script>

</body>
</html>