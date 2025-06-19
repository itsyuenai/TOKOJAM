<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Watch - Luxury Timepieces Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.0/cdn.min.js" defer></script> --}}
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
        .rating-stars {
            color: #ffd700;
        }
        .category-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50" x-data="watchStore()">
    
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">ID Watch</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button @click="showFavorites = !showFavorites" class="p-2 text-gray-600 hover:text-red-500 transition duration-200">
                            <i class="fas fa-heart text-xl"></i>
                            <span x-show="favorites.length > 0" class="cart-badge" x-text="favorites.length"></span>
                        </button>
                    </div>
                    <div class="relative">
                        <button @click="showCart = !showCart" class="p-2 text-gray-600 hover:text-orange-500 transition duration-200">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            <span x-show="cart.length > 0" class="cart-badge" x-text="cart.length"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-bg h-96 flex items-center justify-center text-white">
        <div class="text-center">
            <h2 class="text-5xl font-bold mb-4">
                Discover Timeless
                <span class="text-orange-400">Luxury</span> 
            </h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Jelajahi koleksi eksklusif jam tangan premium merk terkemuka dunia
            </p>
            <div class="space-x-4">
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition duration-300">
                    Lihat Koleksi
                </button>
                <button class="border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-3 rounded-lg font-semibold transition duration-300">
                    Pelajari Lebih Lanjut
                </button>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap gap-4 mb-8 items-center justify-between">
            <div class="flex flex-wrap gap-4">
                <select x-model="selectedCategory" @change="filterWatches()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Kategori</option>
                    <option value="smart">Smart Watch</option>
                    <option value="luxury">Luxury</option>
                    <option value="classic">Classic</option>
                    <option value="sport">Sport</option>
                    <option value="vintage">Vintage</option>
                </select>
                
                <select x-model="priceRange" @change="filterWatches()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Harga</option>
                    <option value="0-500">Rp 0 - Rp 5.000.000</option>
                    <option value="500-1000">Rp 5.000.000 - Rp 10.000.000</option>
                    <option value="1000-2000">Rp 10.000.000 - Rp 20.000.000</option>
                    <option value="2000+">Rp 20.000.000+</option>
                </select>
            </div>
            
            <div class="flex items-center gap-4">
                <label class="text-gray-700 font-medium">Urutkan:</label>
                <select x-model="sortBy" @change="sortWatches()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-orange-50">
                    <option value="name-asc">Nama A-Z</option>
                    <option value="name-desc">Nama Z-A</option>
                    <option value="price-asc">Harga: Terendah ke Tertinggi</option>
                    <option value="price-desc">Harga: Tertinggi ke Terendah</option>
                    <option value="rating-desc">Rating Tertinggi</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Koleksi Premium</h3>
            <p class="text-gray-600" x-text="`${filteredWatches.length} jam ditemukan`"></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="watch in filteredWatches" :key="watch.id">
                <div class="watch-card rounded-xl overflow-hidden">
                    <div class="relative">
                        <img :src="watch.image" :alt="watch.name" class="w-full h-64 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-badge" x-text="watch.category"></span>
                        </div>
                        <div class="absolute top-4 right-4">
                            <button @click="toggleFavorite(watch)" 
                                    :class="isFavorite(watch.id) ? 'text-red-500' : 'text-black hover:text-red-500'"
                                    class="text-xl cursor-pointer transition duration-200">
                                <i :class="isFavorite(watch.id) ? 'fas fa-heart' : 'far fa-heart'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <h4 class="font-bold text-lg text-gray-900 mb-2" x-text="watch.name"></h4>
                        
                        <div class="flex items-center mb-3">
                            <div class="rating-stars mr-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="text-gray-600 text-sm" x-text="`(${watch.reviews})`"></span>
                        </div>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="price-tag px-3 py-1 rounded-lg text-lg font-bold" x-text="formatPrice(watch.price)"></div>
                                <div x-show="watch.originalPrice" class="text-gray-500 line-through text-sm mt-1" x-text="watch.originalPrice ? formatPrice(watch.originalPrice) : ''"></div>
                            </div>
                        </div>
                        
                        <button @click="addToCart(watch)" 
                                class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white py-2 px-4 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            <span x-show="!isInCart(watch.id)">Tambah ke Keranjang</span>
                            <span x-show="isInCart(watch.id)" class="flex items-center justify-center">
                                <i class="fas fa-check mr-2"></i>
                                Sudah di Keranjang
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showCart" x-transition:enter="transform transition ease-in-out duration-300" 
          x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-300"
          x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
          class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl z-50 overflow-y-auto">
        
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-gray-900">Keranjang Belanja</h3>
                <button @click="showCart = false" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div x-show="cart.length === 0" class="text-center text-gray-500">
                <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                <p>Keranjang kosong</p>
            </div>
            
            <template x-for="item in cart" :key="item.id">
                <div class="flex items-center space-x-4 mb-4 p-4 border rounded-lg">
                    <img :src="item.image" :alt="item.name" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm" x-text="item.name"></h4>
                        <p class="text-orange-600 font-bold text-sm" x-text="formatPrice(item.price)"></p>
                        <div class="flex items-center mt-2">
                            <button @click.stop="decrementQuantity(item.id)" class="bg-gray-200 px-2 py-1 rounded">-</button>
                            <span class="mx-2" x-text="item.quantity"></span>
                            <button @click.stop="incrementQuantity(item.id)" class="bg-gray-200 px-2 py-1 rounded">+</button>
                        </div>
                    </div>
                    <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </template>
            
            <div x-show="cart.length > 0" class="border-t pt-4 mt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-bold">Total:</span>
                    <span class="text-xl font-bold text-orange-600" x-text="formatPrice(cartTotal)"></span>
                </div>
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-semibold">
                    Checkout
                </button>
            </div>
        </div>
    </div>

    <div x-show="showFavorites" x-transition:enter="transform transition ease-in-out duration-300" 
          x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-300"
          x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
          class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl z-50 overflow-y-auto">
        
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-gray-900">Favorit</h3>
                <button @click="showFavorites = false" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div x-show="favorites.length === 0" class="text-center text-gray-500">
                <i class="fas fa-heart text-4xl mb-4"></i>
                <p>Belum ada favorit</p>
            </div>
            
            <template x-for="item in favorites" :key="item.id">
                <div class="flex items-center space-x-4 mb-4 p-4 border rounded-lg">
                    <img :src="item.image" :alt="item.name" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm" x-text="item.name"></h4>
                        <p class="text-orange-600 font-bold text-sm" x-text="formatPrice(item.price)"></p>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <button @click="addToCart(item)" class="bg-orange-500 text-white px-3 py-1 rounded text-xs">
                            Cart
                        </button>
                        <button @click="toggleFavorite(item)" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showCart || showFavorites" @click="showCart = false; showFavorites = false" 
          class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>

    <div x-show="notification.show" x-transition:enter="slide-in" x-transition:leave="opacity-0"
          class="notification bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <p x-text="notification.message"></p>
    </div>

    <div class="bg-gray-50 py-16 mt-12">
        <div class="container mx-auto text-center max-w-3xl px-4">
            <i class="fas fa-envelope text-6xl text-orange-500 mb-6"></i>
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Stay Updated with ID Watch</h2>
            <p class="text-lg text-gray-600 mb-8">Get exclusive access to new collections, special offers, and luxury watch insights delivered to your inbox.</p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <input type="email" placeholder="Enter your email address" class="py-3 px-6 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 w-full sm:max-w-md">
                <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-full transition duration-300 shadow-md">Subscribe</button>
            </div>
        </div>
    </div>

    <footer class="bg-gray-900 text-gray-300 py-10 mt-12">
        <div class="container mx-auto text-center px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">ID Watch</h3>
                    <p class="text-sm">Luxury timepieces crafted with precision and passion.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">Quick Links</h3>
                    <ul>
                        <li><a href="#" class="hover:text-orange-500 transition duration-200">Shop</a></li>
                        <li><a href="#" class="hover:text-orange-500 transition duration-200">About Us</a></li>
                        <li><a href="#" class="hover:text-orange-500 transition duration-200">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">Follow Us</h3>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="hover:text-orange-500 transition duration-200"><i class="fab fa-facebook-f"></i></a> 
                        <a href="https://instagram.com/naisyayuen" target="_blank" class="hover:text-orange-500 transition duration-200"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="hover:text-orange-500 transition duration-200"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-gray-700 my-8">
            <p class="text-sm">&copy; 2025 ID Watch. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function watchStore() {
            return {
                selectedCategory: '',
                priceRange: '',
                sortBy: 'name-asc',
                showCart: false,
                showFavorites: false,
                cart: [],
                favorites: [],
                notification: {
                    show: false,
                    message: ''
                },
                watches: [
                    {
                        id: 1,
                        name: 'Digitec Nexus',
                        category: 'smart',
                        price: 1218000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 324,
                        image: 'https://dynamic.zacdn.com/SbEiEzmSmM15ubqYPCtAcGauGdI=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/digitec-6568-2226494-1.jpg'
                    },
                    {
                        id: 2,
                        name: 'Rolex Submariner',
                        category: 'luxury',
                        price: 230000000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 87,
                        image: 'https://images.voila.id/pr:sharp/rs:fit:2048/plain/https%3A%2F%2Fassets.voila.id%2Fvoila%2Fimages%2Fproduct%2Frolex%2F2product-126610LN-Xms-2022-05-30T1259420700.jpeg@webp'
                    },
                    {
                        id: 3,
                        name: 'Orient Envoy Open Heart',
                        category: 'classic',
                        price: 4121000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 78,
                        image: 'https://dynamic.zacdn.com/PKFfsuhHOjVFpcfyOGuD9wRC73k=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/orient-4255-4136864-1.jpg'
                    },
                    {
                        id: 4,
                        name: 'Casio G-Shock GA-700',
                        category: 'sport',
                        price: 1075000,
                        originalPrice: 1850000,
                        rating: 4,
                        reviews: 267,
                        image: 'https://www.jamcasio.com/image/cache/catalog/product/G-SHOCK-GA-700-1BDR-500x500.jpg'
                    },
                    {
                        id: 5,
                        name: 'Seiko Prospex Speedtimer',
                        category: 'chronograph',
                        price: 7870000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 156,
                        image: 'https://www.nzwatches.com/media/catalog/product/cache/f20a3dad9a18e1857a6578422466bfb5/s/s/ssc813p1_00.jpg'
                    },
                    {
                        id: 6,
                        name: 'TAG Heuer Formula 1',
                        category: 'sport',
                        price: 33500000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 95,
                        image: 'https://www.tagheuer.com/on/demandware.static/-/Sites-tagheuer-master/default/dwd6327dc6/TAG_Heuer_Formula_1/WAZ1110.FT8023/WAZ1110.FT8023_1000.png'
                    },
                    {
                        id: 7,
                        name: 'Tissot Gentleman Powermatic 80',
                        category: 'classic',
                        price: 15150000,
                        originalPrice: null,
                        rating: 4,
                        reviews: 123,
                        image: 'https://wornandwound.com/library/uploads/2020/02/Tissot-Gentleman-Powermatic-80-Silicium-6-scaled.jpg'
                    },
                    {
                        id: 8,
                        name: 'Vintage Omega Seamaster',
                        category: 'vintage',
                        price: 9000000,
                        originalPrice: 11200000,
                        rating: 4,
                        reviews: 65,
                        image: 'https://static.wixstatic.com/media/a954ba_0f2d2ee1265541bdb4bb65317a3cf18e~mv2.jpeg/v1/fill/w_514,h_514,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/a954ba_0f2d2ee1265541bdb4bb65317a3cf18e~mv2.jpeg'
                    }
                ],
                filteredWatches: [],
                
                init() {
                    this.filteredWatches = [...this.watches];
                    this.sortWatches();
                },
                
                filterWatches() {
                    this.filteredWatches = this.watches.filter(watch => {
                        const categoryMatch = !this.selectedCategory || watch.category === this.selectedCategory;
                        
                        let priceMatch = true;
                        if (this.priceRange) {
                            const price = watch.price / 1000000;
                            if (this.priceRange === '0-500') priceMatch = price <= 5;
                            else if (this.priceRange === '500-1000') priceMatch = price > 5 && price <= 10;
                            else if (this.priceRange === '1000-2000') priceMatch = price > 10 && price <= 20;
                            else if (this.priceRange === '2000+') priceMatch = price > 20;
                        }
                        
                        return categoryMatch && priceMatch;
                    });
                    
                    this.sortWatches();
                },
                
                sortWatches() {
                    this.filteredWatches.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'name-asc':
                                return a.name.localeCompare(b.name);
                            case 'name-desc':
                                return b.name.localeCompare(a.name);
                            case 'price-asc':
                                return a.price - b.price;
                            case 'price-desc':
                                return b.price - a.price;
                            case 'rating-desc':
                                return b.rating - a.rating;
                            default:
                                return 0;
                        }
                    });
                },
                
                formatPrice(price) {
                    return 'Rp ' + price.toLocaleString('id-ID');
                },

                // Cart Functions
                addToCart(watch) {
                    const existingItem = this.cart.find(item => item.id === watch.id);
                    console.log('addToCart called for:', watch.name);
                    if (existingItem) {
                        existingItem.quantity++;
                        this.showNotification(`Jumlah untuk "${existingItem.name}" telah diperbarui.`);
                    } else {
                        this.cart.push({ ...watch, quantity: 1 });
                        this.showNotification(`"${watch.name}" telah ditambahkan ke keranjang.`);
                    }
                },

                removeFromCart(watchId) {
                    this.cart = this.cart.filter(item => item.id !== watchId);
                    this.showNotification('Item dihapus dari keranjang.');
                },

                // NEW: Dedicated increment and decrement functions
                incrementQuantity(watchId) {
                        console.log('incrementQuantity called for ID:', watchId);
                    const item = this.cart.find(item => item.id === watchId);
                    
                    if (item) {
                        item.quantity++;
                        this.showNotification(`Jumlah untuk "${item.name}" ditambah menjadi ${item.quantity}.`);
                    }
                },

                decrementQuantity(watchId) {
                    const item = this.cart.find(item => item.id === watchId);
                    if (item) {
                        if (item.quantity - 1 <= 0) {
                            this.removeFromCart(watchId);
                            this.showNotification(`"${item.name}" dihapus dari keranjang.`);
                        } else {
                            item.quantity--;
                            this.showNotification(`Jumlah untuk "${item.name}" dikurangi menjadi ${item.quantity}.`);
                        }
                    }
                },

                // The original updateQuantity is no longer directly used by buttons,
                // but if you have other parts of your UI that might set a quantity directly,
                // you could keep it for that. Otherwise, you can remove it.
                // For now, let's keep it but note its limited use.
                // updateQuantity(watchId, newQuantity) {
                //     if (newQuantity <= 0) {
                //         this.removeFromCart(watchId);
                //         return;
                //     }
                //     const item = this.cart.find(item => item.id === watchId);
                //     if (item) {
                //         item.quantity = newQuantity;
                //         this.showNotification(`Jumlah untuk "${item.name}" diperbarui menjadi ${item.quantity}.`);
                //     }
                // },

                isInCart(watchId) {
                    return this.cart.some(item => item.id === watchId);
                },

                get cartTotal() {
                    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
                },

                // Favorites Functions
                toggleFavorite(watch) {
                    const index = this.favorites.findIndex(fav => fav.id === watch.id);
                    if (index > -1) {
                        const watchName = this.favorites[index].name;
                        this.favorites.splice(index, 1);
                        this.showNotification(`"${watchName}" dihapus dari favorit.`);
                    } else {
                        this.favorites.push({ ...watch });
                        this.showNotification(`"${watch.name}" ditambahkan ke favorit!`);
                    }
                },

                isFavorite(watchId) {
                    return this.favorites.some(item => item.id === watchId);
                },

                // Notification Function (renamed for clarity)
                showNotification(message) { // Renamed from existingItem to showNotification
                    this.notification.message = message;
                    this.notification.show = true;
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html>