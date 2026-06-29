import './bootstrap';

window.watchStore = function (initialWatches = []) {
    return {
        selectedCategory: '',
        priceRange: '',
        sortBy: 'name-asc',
        showCart: false,
        showFavorites: false,
        isCheckingOut: false,
        customerName: '',
        checkoutError: '',
        cart: [],
        favorites: [],
        notification: {
            show: false,
            type: 'success',
            message: '',
        },
        watches: initialWatches.map((watch) => ({
            ...watch,
            price: Number(watch.price),
            stock: Number(watch.stock),
        })),
        filteredWatches: [],

        init() {
            this.filteredWatches = [...this.watches];
            this.sortWatches();
        },

        get categories() {
            return [...new Map(
                this.watches.map((watch) => [watch.category, watch.categoryName]),
            )].map(([value, label]) => ({ value, label }));
        },

        get cartTotal() {
            return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        },

        get cartItemCount() {
            return this.cart.reduce((count, item) => count + item.quantity, 0);
        },

        filterWatches() {
            this.filteredWatches = this.watches.filter((watch) => {
                const categoryMatches = !this.selectedCategory || watch.category === this.selectedCategory;
                const priceMatches = this.matchesPriceRange(watch.price);

                return categoryMatches && priceMatches;
            });

            this.sortWatches();
        },

        matchesPriceRange(price) {
            if (!this.priceRange) {
                return true;
            }

            if (this.priceRange === 'under-2m') {
                return price < 2000000;
            }

            if (this.priceRange === '2m-5m') {
                return price >= 2000000 && price <= 5000000;
            }

            if (this.priceRange === '5m-20m') {
                return price > 5000000 && price <= 20000000;
            }

            return price > 20000000;
        },

        sortWatches() {
            this.filteredWatches.sort((a, b) => {
                if (this.sortBy === 'name-desc') {
                    return b.name.localeCompare(a.name);
                }

                if (this.sortBy === 'price-asc') {
                    return a.price - b.price;
                }

                if (this.sortBy === 'price-desc') {
                    return b.price - a.price;
                }

                return a.name.localeCompare(b.name);
            });
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(price);
        },

        addToCart(watch) {
            if (watch.stock <= 0) {
                this.showNotification(`${watch.name} sedang habis.`, 'error');
                return;
            }

            const existingItem = this.cart.find((item) => item.id === watch.id);

            if (existingItem) {
                if (existingItem.quantity >= watch.stock) {
                    this.showNotification(`Stok ${watch.name} hanya ${watch.stock}.`, 'error');
                    return;
                }

                existingItem.quantity += 1;
            } else {
                this.cart.push({ ...watch, quantity: 1 });
            }

            this.checkoutError = '';
            this.showCart = true;
            this.showNotification(`${watch.name} ditambahkan ke keranjang.`);
        },

        removeFromCart(watchId) {
            this.cart = this.cart.filter((item) => item.id !== watchId);
        },

        incrementQuantity(watchId) {
            const item = this.cart.find((cartItem) => cartItem.id === watchId);

            if (!item) {
                return;
            }

            if (item.quantity >= item.stock) {
                this.showNotification(`Stok ${item.name} hanya ${item.stock}.`, 'error');
                return;
            }

            item.quantity += 1;
        },

        decrementQuantity(watchId) {
            const item = this.cart.find((cartItem) => cartItem.id === watchId);

            if (!item) {
                return;
            }

            if (item.quantity <= 1) {
                this.removeFromCart(watchId);
                return;
            }

            item.quantity -= 1;
        },

        isInCart(watchId) {
            return this.cart.some((item) => item.id === watchId);
        },

        toggleFavorite(watch) {
            const existingIndex = this.favorites.findIndex((item) => item.id === watch.id);

            if (existingIndex >= 0) {
                this.favorites.splice(existingIndex, 1);
                return;
            }

            this.favorites.push({ ...watch });
        },

        isFavorite(watchId) {
            return this.favorites.some((item) => item.id === watchId);
        },

        async checkout() {
            if (this.cart.length === 0) {
                this.checkoutError = 'Keranjang belanja masih kosong.';
                return;
            }

            this.isCheckingOut = true;
            this.checkoutError = '';

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        customer_name: this.customerName || null,
                        items: this.cart.map((item) => ({
                            product_id: item.id,
                            quantity: item.quantity,
                        })),
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.checkoutMessage(payload));
                }

                this.applySuccessfulCheckout();
                this.showCart = false;
                this.customerName = '';
                this.showNotification(payload.message || 'Checkout berhasil.');
            } catch (error) {
                this.checkoutError = error.message || 'Checkout gagal diproses.';
                this.showNotification(this.checkoutError, 'error');
            } finally {
                this.isCheckingOut = false;
            }
        },

        checkoutMessage(payload) {
            if (payload?.stock) {
                return `${payload.stock.product_name} hanya tersedia ${payload.stock.available_stock} unit.`;
            }

            if (payload?.errors) {
                return Object.values(payload.errors).flat()[0] || payload.message;
            }

            return payload?.message || 'Checkout gagal diproses.';
        },

        applySuccessfulCheckout() {
            this.cart.forEach((cartItem) => {
                const watch = this.watches.find((item) => item.id === cartItem.id);

                if (watch) {
                    watch.stock = Math.max(0, watch.stock - cartItem.quantity);
                }
            });

            this.cart = [];
            this.filterWatches();
        },

        showNotification(message, type = 'success') {
            this.notification = { show: true, type, message };

            window.clearTimeout(this.notificationTimer);
            this.notificationTimer = window.setTimeout(() => {
                this.notification.show = false;
            }, 3000);
        },
    };
};
