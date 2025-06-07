{{-- Ini adalah tampilan untuk komponen Livewire Pos --}}
{{-- Pastikan @livewireStyles di head dan @livewireScripts di body end dari layout utama jika ada --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem POS | WatchHaus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles {{-- PENTING: Untuk CSS Livewire --}}
</head>
<body class="antialiased font-sans bg-gray-100 text-gray-900">

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header POS -->
        <header class="sticky top-0 z-50 bg-white shadow-md p-4">
            <div class="container mx-auto flex items-center justify-between">
                <div class="flex items-center">
                    <x-heroicon-o-cash class="text-blue-600 w-8 h-8 mr-3"/>
                    <span class="font-bold text-2xl text-gray-900">ID Watch POS</span>
                </div>
                <nav>
                    <a href="{{ route('watch-catalog-page.index') }}" class="text-blue-600 hover:text-blue-800 font-medium px-4 py-2 rounded-md">Katalog</a>
                    <a href="/admin" class="text-blue-600 hover:text-blue-800 font-medium px-4 py-2 rounded-md">Admin Panel</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-6 md:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Kolom Produk untuk Pencarian --}}
                <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 flex flex-col">
                    <h2 class="text-2xl font-semibold mb-5 text-gray-800">Daftar Produk</h2>
                    <div class="mb-4">
                        <input
                            wire:model.live.debounce.400ms="search"
                            type="text"
                            placeholder="Cari jam tangan (nama atau SKU)..."
                            class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-lg"
                        >
                    </div>

                    @if($search && $products->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 overflow-y-auto max-h-[60vh] pr-2">
                            @foreach ($products as $watch)
                                <div class="border border-gray-200 rounded-lg p-4 flex flex-col justify-between items-center text-center shadow-sm hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $watch->name }}</h3>
                                    <p class="text-sm text-gray-600 mb-2">SKU: {{ $watch->sku }}</p>
                                    <p class="text-xl font-bold text-green-700 mb-3">Rp {{ number_format($watch->price, 0, ',', '.') }}</p>
                                    <p class="text-sm text-gray-700">Stok: {{ $watch->stock }}</p>
                                    <button
                                        wire:click="addToCart({{ $watch->id }})"
                                        @if($watch->stock <= 0) disabled @endif
                                        class="mt-3 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        @if($watch->stock <= 0)
                                            Stok Habis
                                        @else
                                            Tambah ke Keranjang
                                        @endif
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @elseif($search && $products->isEmpty())
                        <p class="text-center text-gray-500 mt-8 text-xl">Tidak ada produk yang cocok dengan pencarian Anda.</p>
                    @else
                        <p class="text-center text-gray-500 mt-8 text-xl">Mulai ketik untuk mencari produk...</p>
                    @endif
                </div>

                {{-- Kolom Keranjang dan Checkout --}}
                <div class="lg:col-span-1 bg-white rounded-xl shadow-md p-6 flex flex-col">
                    <h2 class="text-2xl font-semibold mb-5 text-gray-800">Keranjang Belanja</h2>

                    @if(empty($cart))
                        <div class="flex-grow flex items-center justify-center">
                            <p class="text-center text-gray-500 text-lg">Keranjang kosong. Tambahkan produk.</p>
                        </div>
                    @else
                        <div class="flex-grow space-y-4 overflow-y-auto max-h-[45vh] pr-2 mb-4">
                            @foreach ($jamsInCart as $item)
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                        <p class="text-sm text-gray-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input
                                            type="number"
                                            wire:model.live.debounce.300ms="cart.{{ $item['id'] }}.quantity"
                                            min="1"
                                            class="w-20 text-center border border-gray-300 rounded-md py-1 px-2 text-base"
                                            wire:change="updateCartQuantity({{ $item['id'] }}, $event.target.value)"
                                        >
                                        <button
                                            wire:click="removeFromCart({{ $item['id'] }})"
                                            class="ml-3 text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100"
                                        >
                                            <x-heroicon-o-trash class="h-5 w-5"/>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-auto border-t pt-4">
                            <div class="flex justify-between items-center text-xl font-bold text-gray-900 mb-4">
                                <span>Total:</span>
                                <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="customerName" class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan (Opsional):</label>
                                <input
                                    wire:model.live="customerName"
                                    type="text"
                                    id="customerName"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            <button
                                wire:click="checkout"
                                @if(empty($cart)) disabled @endif
                                class="w-full px-6 py-3 bg-green-600 text-white font-bold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Proses Pembayaran
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    @livewireScripts {{-- PENTING: Untuk JavaScript Livewire --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    {{-- Filament Notification --}}
    @livewire('notifications')
</body>
</html>