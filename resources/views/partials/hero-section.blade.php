<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Watch - Luxury Timepieces Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.0/cdn.min.js" defer></script>
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
    </style>
</head>
<body class="bg-gray-50">
            
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

    <!-- Filters & Sorting -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="watchStore()">
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

        <!-- Results Count -->
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Koleksi Premium</h3>
            <p class="text-gray-600" x-text="`${filteredWatches.length} jam ditemukan`"></p>
        </div>

        <!-- Watch Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="watch in filteredWatches" :key="watch.id">
                <div class="watch-card rounded-xl overflow-hidden">
                    <div class="relative">
                        <img :src="watch.image" :alt="watch.name" class="w-full h-64 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-badge" x-text="watch.category"></span>
                        </div>
                        <div class="absolute top-4 right-4">
                            <i class="far fa-heart text-white text-xl hover:text-red-500 cursor-pointer"></i>
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
                        
                        <button class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white py-2 px-4 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function watchStore() {
            return {
                selectedCategory: '',
                priceRange: '',
                sortBy: 'name-asc',
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
                            const price = watch.price / 1000000; // Convert to millions
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
                }
            }
        }
    </script>
</body>
</html>