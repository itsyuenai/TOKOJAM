// Livewire-style Component Architecture for Watch Store
class WatchStore {
    constructor() {
        this.state = {
            watches: [],
            filteredWatches: [],
            cart: [],
            search: '',
            category: '',
            priceRange: '',
            sortBy: 'name',
            loading: false
        };

        this.init();
    }

    // Initialize the application
    init() {
        this.loadWatches();
        this.bindEvents();
        this.render();
    }

    // Load watch data (simulating API call)
    loadWatches() {
        this.state.loading = true;
        this.renderLoading();

        // Simulate API delay
        setTimeout(() => {
            this.state.watches = [
                {
                    id: 1,
                    name: "Rolex Submariner Date",
                    price: 187500000,
                    originalPrice: 202500000,
                    rating: 4.9,
                    reviews: 247,
                    image: "https://images.pexels.com/photos/47856/rolex-wrist-watch-clock-time-47856.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "luxury",
                    inStock: true
                },
                {
                    id: 2,
                    name: "Omega Speedmaster Professional",
                    price: 101250000,
                    rating: 4.8,
                    reviews: 189,
                    image: "https://images.pexels.com/photos/236916/pexels-photo-236916.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "luxury",
                    inStock: true
                },
                {
                    id: 3,
                    name: "TAG Heuer Formula 1",
                    price: 18750000,
                    rating: 4.6,
                    reviews: 95,
                    image: "https://images.pexels.com/photos/125779/pexels-photo-125779.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "sport",
                    inStock: true
                },
                {
                    id: 4,
                    name: "Seiko Prospex Diver",
                    price: 6750000,
                    rating: 4.7,
                    reviews: 156,
                    image: "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "sport",
                    inStock: true
                },
                {
                    id: 5,
                    name: "Cartier Tank Must",
                    price: 48000000,
                    rating: 4.8,
                    reviews: 78,
                    image: "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "classic",
                    inStock: true
                },
                {
                    id: 6,
                    name: "Apple Watch Series 9",
                    price: 5985000,
                    rating: 4.5,
                    reviews: 324,
                    image: "https://images.pexels.com/photos/393047/pexels-photo-393047.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "smart",
                    inStock: true
                },
                {
                    id: 7,
                    name: "Patek Philippe Calatrava",
                    price: 427500000,
                    rating: 5.0,
                    reviews: 42,
                    image: "https://images.pexels.com/photos/432267/pexels-photo-432267.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "luxury",
                    inStock: true
                },
                {
                    id: 8,
                    name: "Casio G-Shock DW-5600",
                    price: 1335000,
                    rating: 4.4,
                    reviews: 267,
                    image: "https://images.pexels.com/photos/1034063/pexels-photo-1034063.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "sport",
                    inStock: true
                },
                {
                    id: 9,
                    name: "Tissot Le Locle Powermatic 80",
                    price: 8625000,
                    rating: 4.6,
                    reviews: 123,
                    image: "https://images.pexels.com/photos/280250/pexels-photo-280250.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "classic",
                    inStock: true
                },
                {
                    id: 10,
                    name: "Breitling Navitimer 8",
                    price: 63750000,
                    rating: 4.7,
                    reviews: 87,
                    image: "https://images.pexels.com/photos/1697358/pexels-photo-1697358.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "luxury",
                    inStock: true
                },
                {
                    id: 11,
                    name: "Hamilton Khaki Field Auto",
                    price: 6675000,
                    rating: 4.5,
                    reviews: 134,
                    image: "https://images.pexels.com/photos/125651/pexels-photo-125651.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "classic",
                    inStock: false
                },
                {
                    id: 12,
                    name: "Vintage Omega Seamaster",
                    price: 27750000,
                    originalPrice: 31500000,
                    rating: 4.8,
                    reviews: 65,
                    image: "https://images.pexels.com/photos/859895/pexels-photo-859895.jpeg?auto=compress&cs=tinysrgb&w=500&h=500&dpr=1",
                    category: "vintage",
                    inStock: true
                }
            ];

            this.state.loading = false;
            this.applyFilters();
        }, 1000);
    }

    // Bind event listeners (Livewire-style wire:model equivalent)
    bindEvents() {
        // Search inputs
        const searchInputs = ['search-input', 'search-input-mobile'];
        searchInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', (e) => {
                    this.state.search = e.target.value;
                    // Sync both search inputs
                    searchInputs.forEach(syncId => {
                        const syncInput = document.getElementById(syncId);
                        if (syncInput && syncInput !== e.target) {
                            syncInput.value = e.target.value;
                        }
                    });
                    this.applyFilters();
                });
            }
        });

        // Filter selects
        document.getElementById('category-filter').addEventListener('change', (e) => {
            this.state.category = e.target.value;
            this.applyFilters();
        });

        document.getElementById('price-filter').addEventListener('change', (e) => {
            this.state.priceRange = e.target.value;
            this.applyFilters();
        });

        document.getElementById('sort-filter').addEventListener('change', (e) => {
            this.state.sortBy = e.target.value;
            this.applyFilters();
        });

        // Cart events
        document.getElementById('cart-button').addEventListener('click', () => {
            this.openCart();
        });

        document.getElementById('close-cart').addEventListener('click', () => {
            this.closeCart();
        });

        document.getElementById('cart-overlay').addEventListener('click', () => {
            this.closeCart();
        });

        // Newsletter form
        document.getElementById('newsletter-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.subscribeNewsletter();
        });
    }

    // Apply filters and sorting (Livewire-style computed property)
    applyFilters() {
        let filtered = [...this.state.watches];

        // Search filter
        if (this.state.search) {
            const searchTerm = this.state.search.toLowerCase();
            filtered = filtered.filter(watch => 
                watch.name.toLowerCase().includes(searchTerm) ||
                watch.category.toLowerCase().includes(searchTerm)
            );
        }

        // Category filter
        if (this.state.category) {
            filtered = filtered.filter(watch => watch.category === this.state.category);
        }

        // Price range filter
        if (this.state.priceRange) {
            filtered = filtered.filter(watch => {
                switch (this.state.priceRange) {
                    case 'under-5m':
                        return watch.price < 5000000;
                    case '5m-15m':
                        return watch.price >= 5000000 && watch.price <= 15000000;
                    case '15m-50m':
                        return watch.price >= 15000000 && watch.price <= 50000000;
                    case 'above-50m':
                        return watch.price > 50000000;
                    default:
                        return true;
                }
            });
        }

        // Sort products
        filtered.sort((a, b) => {
            switch (this.state.sortBy) {
                case 'price-low':
                    return a.price - b.price;
                case 'price-high':
                    return b.price - a.price;
                case 'rating':
                    return b.rating - a.rating;
                case 'name':
                default:
                    return a.name.localeCompare(b.name);
            }
        });

        this.state.filteredWatches = filtered;
        this.render();
    }

    // Render methods (Livewire-style blade templates)
    render() {
        if (this.state.loading) {
            this.renderLoading();
            return;
        }

        this.renderProductGrid();
        this.renderProductCount();
        this.renderCart();
    }

    renderLoading() {
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('product-grid').classList.add('hidden');
        document.getElementById('empty-state').classList.add('hidden');
    }

    renderProductGrid() {
        const grid = document.getElementById('product-grid');
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');

        loadingState.classList.add('hidden');

        if (this.state.filteredWatches.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        grid.classList.remove('hidden');

        grid.innerHTML = this.state.filteredWatches.map(watch => this.renderProductCard(watch)).join('');

        // Re-initialize Lucide icons for new content
        lucide.createIcons();

        // Bind product card events
        this.bindProductCardEvents();
    }

    renderProductCard(watch) {
        const formatPrice = (price) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            }).format(price);
        };

        const renderStars = (rating) => {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<i data-lucide="star" class="h-4 w-4 ${i <= Math.floor(rating) ? 'text-amber-400 fill-current' : 'text-gray-300'}"></i>`;
            }
            return stars;
        };

        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
                <div class="relative">
                    <img
                        src="${watch.image}"
                        alt="${watch.name}"
                        class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <button class="bg-white p-2 rounded-full shadow-lg hover:bg-gray-50 transition-colors duration-200">
                            <i data-lucide="heart" class="h-5 w-5 text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    ${watch.originalPrice ? `
                        <div class="absolute top-3 left-3">
                            <span class="bg-red-500 text-white px-2 py-1 rounded-full text-sm font-semibold">
                                Sale
                            </span>
                        </div>
                    ` : ''}
                    ${!watch.inStock ? `
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                            <span class="bg-white text-gray-800 px-4 py-2 rounded-lg font-semibold">
                                Stok Habis
                            </span>
                        </div>
                    ` : ''}
                </div>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-amber-600 font-medium bg-amber-50 px-2 py-1 rounded-full">
                            ${this.getCategoryName(watch.category)}
                        </span>
                        <div class="flex items-center gap-1">
                            ${renderStars(watch.rating)}
                            <span class="text-sm text-gray-500 ml-1">(${watch.reviews})</span>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2 group-hover:text-amber-600 transition-colors duration-200">
                        ${watch.name}
                    </h3>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl font-bold text-gray-900">
                            ${formatPrice(watch.price)}
                        </span>
                        ${watch.originalPrice ? `
                            <span class="text-lg text-gray-500 line-through">
                                ${formatPrice(watch.originalPrice)}
                            </span>
                        ` : ''}
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button
                            data-action="add-to-cart"
                            data-watch-id="${watch.id}"
                            ${!watch.inStock ? 'disabled' : ''}
                            class="flex-1 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2 px-4 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
                        >
                            <i data-lucide="shopping-cart" class="h-4 w-4"></i>
                            Tambah ke Keranjang
                        </button>
                        <button
                            data-action="buy-now"
                            data-watch-id="${watch.id}"
                            ${!watch.inStock ? 'disabled' : ''}
                            class="flex-1 bg-slate-800 hover:bg-slate-900 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2 px-4 rounded-lg font-semibold transition-all duration-200"
                        >
                            Beli Sekarang
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    bindProductCardEvents() {
        // Add to cart buttons
        document.querySelectorAll('[data-action="add-to-cart"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const watchId = parseInt(e.target.closest('[data-watch-id]').dataset.watchId);
                this.addToCart(watchId);
            });
        });

        // Buy now buttons
        document.querySelectorAll('[data-action="buy-now"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const watchId = parseInt(e.target.closest('[data-watch-id]').dataset.watchId);
                this.buyNow(watchId);
            });
        });
    }

    renderProductCount() {
        const count = this.state.filteredWatches.length;
        document.getElementById('product-count').textContent = 
            `${count} ${count === 1 ? 'jam' : 'jam'} ditemukan`;
    }

    renderCart() {
        const cartCount = this.getTotalItems();
        const cartCountElement = document.getElementById('cart-count');
        const cartItemsCountElement = document.getElementById('cart-items-count');

        // Update cart count badge
        if (cartCount > 0) {
            cartCountElement.textContent = cartCount;
            cartCountElement.classList.remove('hidden');
        } else {
            cartCountElement.classList.add('hidden');
        }

        // Update cart sidebar count
        cartItemsCountElement.textContent = cartCount;

        // Render cart items
        const cartItemsContainer = document.getElementById('cart-items');
        const cartEmpty = document.getElementById('cart-empty');
        const cartFooter = document.getElementById('cart-footer');

        if (this.state.cart.length === 0) {
            cartItemsContainer.classList.add('hidden');
            cartEmpty.classList.remove('hidden');
            cartFooter.classList.add('hidden');
        } else {
            cartEmpty.classList.add('hidden');
            cartItemsContainer.classList.remove('hidden');
            cartFooter.classList.remove('hidden');

            cartItemsContainer.innerHTML = this.state.cart.map(item => this.renderCartItem(item)).join('');
            
            // Update total
            const total = this.getCartTotal();
            document.getElementById('cart-total').textContent = this.formatPrice(total);

            // Re-initialize Lucide icons
            lucide.createIcons();

            // Bind cart item events
            this.bindCartItemEvents();
        }
    }

    renderCartItem(item) {
        return `
            <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
                <img
                    src="${item.image}"
                    alt="${item.name}"
                    class="w-16 h-16 object-cover rounded-lg"
                />
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 text-sm mb-1">${item.name}</h3>
                    <p class="text-amber-600 font-bold">${this.formatPrice(item.price)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        data-action="decrease-quantity"
                        data-item-id="${item.id}"
                        class="p-1 hover:bg-gray-100 rounded-full transition-colors duration-200"
                    >
                        <i data-lucide="minus" class="h-4 w-4 text-gray-600"></i>
                    </button>
                    <span class="w-8 text-center font-semibold">${item.quantity}</span>
                    <button
                        data-action="increase-quantity"
                        data-item-id="${item.id}"
                        class="p-1 hover:bg-gray-100 rounded-full transition-colors duration-200"
                    >
                        <i data-lucide="plus" class="h-4 w-4 text-gray-600"></i>
                    </button>
                </div>
                <button
                    data-action="remove-item"
                    data-item-id="${item.id}"
                    class="p-2 hover:bg-red-50 rounded-full transition-colors duration-200"
                >
                    <i data-lucide="x" class="h-4 w-4 text-red-500"></i>
                </button>
            </div>
        `;
    }

    bindCartItemEvents() {
        // Quantity controls
        document.querySelectorAll('[data-action="increase-quantity"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const itemId = parseInt(e.target.closest('[data-item-id]').dataset.itemId);
                this.updateQuantity(itemId, 1);
            });
        });

        document.querySelectorAll('[data-action="decrease-quantity"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const itemId = parseInt(e.target.closest('[data-item-id]').dataset.itemId);
                this.updateQuantity(itemId, -1);
            });
        });

        // Remove item
        document.querySelectorAll('[data-action="remove-item"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const itemId = parseInt(e.target.closest('[data-item-id]').dataset.itemId);
                this.removeFromCart(itemId);
            });
        });
    }

    // Cart methods (Livewire-style actions)
    addToCart(watchId) {
        const watch = this.state.watches.find(w => w.id === watchId);
        if (!watch || !watch.inStock) return;

        const existingItem = this.state.cart.find(item => item.id === watchId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.state.cart.push({
                id: watch.id,
                name: watch.name,
                price: watch.price,
                image: watch.image,
                quantity: 1
            });
        }

        this.renderCart();
        this.showNotification('Produk berhasil ditambahkan ke keranjang!');
    }

    updateQuantity(itemId, change) {
        const item = this.state.cart.find(item => item.id === itemId);
        if (!item) return;

        item.quantity += change;

        if (item.quantity <= 0) {
            this.removeFromCart(itemId);
        } else {
            this.renderCart();
        }
    }

    removeFromCart(itemId) {
        this.state.cart = this.state.cart.filter(item => item.id !== itemId);
        this.renderCart();
        this.showNotification('Produk dihapus dari keranjang');
    }

    buyNow(watchId) {
        this.addToCart(watchId);
        this.openCart();
        this.showNotification('Proses pembelian langsung!');
    }

    getTotalItems() {
        return this.state.cart.reduce((total, item) => total + item.quantity, 0);
    }

    getCartTotal() {
        return this.state.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // UI methods
    openCart() {
        document.getElementById('cart-sidebar').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    closeCart() {
        document.getElementById('cart-sidebar').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    subscribeNewsletter() {
        const email = document.getElementById('newsletter-email').value;
        
        // Simulate API call
        setTimeout(() => {
            document.getElementById('newsletter-form').classList.add('hidden');
            document.getElementById('newsletter-success').classList.remove('hidden');
            
            // Reset after 3 seconds
            setTimeout(() => {
                document.getElementById('newsletter-form').classList.remove('hidden');
                document.getElementById('newsletter-success').classList.add('hidden');
                document.getElementById('newsletter-email').value = '';
            }, 3000);
        }, 500);
    }

    showNotification(message) {
        const notification = document.getElementById('notification');
        const messageElement = document.getElementById('notification-message');
        
        messageElement.textContent = message;
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Helper methods
    getCategoryName(category) {
        const categories = {
            luxury: 'Mewah',
            sport: 'Olahraga',
            classic: 'Klasik',
            smart: 'Jam Pintar',
            vintage: 'Vintage'
        };
        return categories[category] || category;
    }

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(price);
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.watchStore = new WatchStore();
});