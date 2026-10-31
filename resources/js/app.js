import './bootstrap';

window.watchStore = function (initialWatches = []) {
    return {
        selectedCategory: '',
        priceRange: '',
        sortBy: 'name-asc',
        showCart: false,
        checkoutStep: false,
        showFavorites: false,
        showHelpdesk: false,
        isSendingHelpdesk: false,
        helpdeskQuestion: '',
        helpdeskError: '',
        helpdeskMessages: [{
            role: 'assistant',
            content: 'Halo! Saya asisten TOKOJAM. Saya bisa membantu mencari informasi produk, cara belanja, dan langkah cek ongkir.',
        }],
        isCheckingOut: false,
        isSubmittingComplaint: false,
        customerName: '',
        customerEmail: '',
        customerPhone: '',
        shippingAddress: '',
        shippingPostalCode: '',
        shippingRates: [],
        selectedShippingRate: '',
        isLoadingShippingRates: false,
        checkoutError: '',
        csrfMismatch: false,
        complaintError: '',
        complaintSuccess: '',
        complaintForm: {
            customer_name: '',
            email: '',
            order_reference: '',
            message: '',
        },
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
            this.restoreCart();
            this.filteredWatches = [...this.watches];
            this.sortWatches();
        },

        invalidateShippingRates() {
            this.shippingRates = [];
            this.selectedShippingRate = '';
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

        get selectedItems() {
            return this.cart.filter((item) => item.selected);
        },

        get selectedCartItemCount() {
            return this.selectedItems.reduce((count, item) => count + item.quantity, 0);
        },

        get selectedCartTotal() {
            return this.selectedItems.reduce((total, item) => total + item.price * item.quantity, 0);
        },

        get selectedShipping() {
            return this.shippingRates.find((rate) =>
                `${rate.courier_company}:${rate.courier_type}` === this.selectedShippingRate,
            );
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
                existingItem.selected = true;
            } else {
                this.cart.push({ ...watch, quantity: 1, selected: true });
            }

            this.persistCart();
            this.invalidateShippingRates();
            this.checkoutError = '';
            this.showCart = true;
            this.showNotification(`${watch.name} ditambahkan ke keranjang.`);
        },

        removeFromCart(watchId) {
            this.cart = this.cart.filter((item) => item.id !== watchId);
            this.persistCart();
            this.invalidateShippingRates();
        },

        cartSelectionChanged() {
            this.persistCart();
            this.invalidateShippingRates();
            this.checkoutError = '';
        },

        restoreCart() {
            const storedCart = sessionStorage.getItem('tokojam-cart');

            if (!storedCart) {
                return;
            }

            try {
                const savedItems = JSON.parse(storedCart);
                if (!Array.isArray(savedItems)) {
                    throw new Error('Format keranjang tidak valid.');
                }

                this.cart = savedItems.flatMap((savedItem) => {
                    const watch = this.watches.find((item) => item.id === Number(savedItem.id));
                    const quantity = Number(savedItem.quantity);

                    if (!watch || !Number.isInteger(quantity) || quantity < 1 || quantity > watch.stock) {
                        return [];
                    }

                    return [{ ...watch, quantity, selected: savedItem.selected !== false }];
                });
            } catch (error) {
                sessionStorage.removeItem('tokojam-cart');
                this.showNotification('Keranjang sebelumnya tidak dapat dipulihkan.', 'error');
            }
        },

        persistCart() {
            sessionStorage.setItem('tokojam-cart', JSON.stringify(this.cart.map((item) => ({
                id: item.id,
                quantity: item.quantity,
                selected: item.selected,
            }))));
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
            this.persistCart();
            this.invalidateShippingRates();
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
            this.persistCart();
            this.invalidateShippingRates();
        },

        isInCart(watchId) {
            return this.cart.some((item) => item.id === watchId);
        },

        async fetchShippingRates() {
            if (!this.customerName.trim()
                || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.customerEmail)
                || !this.customerPhone.trim()
                || !this.shippingAddress.trim()) {
                this.checkoutError = 'Isi nama, email, nomor WhatsApp, dan alamat lengkap terlebih dahulu.';
                return;
            }

            if (this.selectedItems.length === 0) {
                this.checkoutError = 'Pilih setidaknya satu barang untuk dihitung ongkirnya.';
                return;
            }

            if (!/^[0-9]{5}$/.test(this.shippingPostalCode)) {
                this.checkoutError = 'Masukkan kode pos tujuan 5 digit.';
                return;
            }

            this.isLoadingShippingRates = true;
            this.checkoutError = '';
            this.csrfMismatch = false;
            this.shippingRates = [];
            this.selectedShippingRate = '';

            try {
                const response = await fetch('/shipping/rates', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        destination_postal_code: this.shippingPostalCode,
                        items: this.selectedItems.map((item) => ({
                            product_id: item.id,
                            quantity: item.quantity,
                        })),
                    }),
                });

                if (response.status === 419) {
                    this.checkoutError = 'Sesi keamanan kedaluwarsa. Muat ulang halaman untuk melanjutkan.';
                    this.csrfMismatch = true;
                    return;
                }

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.responseMessage(payload, 'Tarif kurir gagal dimuat.'));
                }

                this.shippingRates = payload.rates || [];
                if (this.shippingRates.length === 0) {
                    this.checkoutError = 'Tidak ada layanan kurir untuk alamat tersebut.';
                }
            } catch (error) {
                this.checkoutError = error.message || 'Tarif kurir gagal dimuat.';
            } finally {
                this.isLoadingShippingRates = false;
            }
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
            if (this.selectedItems.length === 0) {
                this.checkoutError = 'Pilih setidaknya satu barang yang ingin di-checkout.';
                return;
            }

            this.checkoutError = '';
            this.checkoutStep = true;
        },

        backToCart() {
            this.checkoutStep = false;
            this.checkoutError = '';
            this.csrfMismatch = false;
            this.invalidateShippingRates();
        },

        async placeOrder() {
            if (this.selectedItems.length === 0) {
                this.checkoutError = 'Pilih setidaknya satu barang untuk checkout.';
                this.backToCart();
                return;
            }

            if (!this.customerName.trim()
                || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.customerEmail)
                || !this.customerPhone.trim()
                || !this.shippingAddress.trim()
                || !/^[0-9]{5}$/.test(this.shippingPostalCode)) {
                this.checkoutError = 'Lengkapi nama, email, nomor WhatsApp, alamat, dan kode pos 5 digit.';
                return;
            }

            if (!this.selectedShipping) {
                this.checkoutError = 'Pilih layanan kurir sebelum melanjutkan.';
                return;
            }

            this.isCheckingOut = true;
            this.checkoutError = '';
            this.csrfMismatch = false;

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        customer_name: this.customerName,
                        customer_email: this.customerEmail,
                        customer_phone: this.customerPhone,
                        shipping_address: this.shippingAddress,
                        shipping_postal_code: this.shippingPostalCode,
                        shipping_courier: this.selectedShipping.courier_company,
                        shipping_service: this.selectedShipping.courier_type,
                        items: this.selectedItems.map((item) => ({
                            product_id: item.id,
                            quantity: item.quantity,
                        })),
                    }),
                });

                if (response.status === 419) {
                    this.checkoutError = 'Sesi keamanan kedaluwarsa. Muat ulang halaman untuk melanjutkan.';
                    this.csrfMismatch = true;
                    return;
                }

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.checkoutMessage(payload));
                }

                if (!payload.order?.payment_url) {
                    throw new Error('Pesanan dibuat tetapi tautan pembayaran tidak tersedia. Hubungi admin toko.');
                }

                this.applySuccessfulCheckout();
                window.location.assign(payload.order.payment_url);
            } catch (error) {
                this.checkoutError = error.message || 'Checkout gagal diproses.';
                this.showNotification(this.checkoutError, 'error');
            } finally {
                this.isCheckingOut = false;
            }
        },

        async submitComplaint() {
            this.isSubmittingComplaint = true;
            this.complaintError = '';
            this.complaintSuccess = '';

            try {
                const response = await fetch('/complaints', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        customer_name: this.complaintForm.customer_name,
                        email: this.complaintForm.email,
                        order_reference: this.complaintForm.order_reference || null,
                        message: this.complaintForm.message,
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.responseMessage(payload, 'Keluhan gagal dikirim.'));
                }

                this.resetComplaintForm();
                this.complaintSuccess = payload.message || 'Keluhan berhasil dikirim.';
                this.showNotification(this.complaintSuccess);
            } catch (error) {
                this.complaintError = error.message || 'Keluhan gagal dikirim.';
                this.showNotification(this.complaintError, 'error');
            } finally {
                this.isSubmittingComplaint = false;
            }
        },

        async sendHelpdeskMessage() {
            const content = this.helpdeskQuestion.trim();

            if (!content || this.isSendingHelpdesk) {
                return;
            }

            this.helpdeskMessages.push({ role: 'user', content });
            this.helpdeskQuestion = '';
            this.helpdeskError = '';
            this.isSendingHelpdesk = true;
            this.scrollHelpdeskToBottom();

            try {
                const response = await fetch('/helpdesk/chat', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        messages: this.helpdeskMessages.slice(-8),
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.responseMessage(payload, 'Asisten belum dapat menjawab. Silakan coba lagi.'));
                }

                this.helpdeskMessages.push({ role: 'assistant', content: payload.reply });
            } catch (error) {
                this.helpdeskError = error.message || 'Asisten belum dapat menjawab. Silakan coba lagi.';
            } finally {
                this.isSendingHelpdesk = false;
                this.scrollHelpdeskToBottom();
            }
        },

        scrollHelpdeskToBottom() {
            this.$nextTick(() => {
                const messages = this.$refs.helpdeskMessagesList;

                if (messages) {
                    messages.scrollTop = messages.scrollHeight;
                }
            });
        },

        checkoutMessage(payload) {
            if (payload?.stock) {
                return `${payload.stock.product_name} hanya tersedia ${payload.stock.available_stock} unit.`;
            }

            return this.responseMessage(payload, 'Checkout gagal diproses.');
        },

        responseMessage(payload, fallback) {
            if (payload?.errors) {
                return Object.values(payload.errors).flat()[0] || payload.message;
            }

            return payload?.message || fallback;
        },

        applySuccessfulCheckout() {
            this.selectedItems.forEach((cartItem) => {
                const watch = this.watches.find((item) => item.id === cartItem.id);

                if (watch) {
                    watch.stock = Math.max(0, watch.stock - cartItem.quantity);
                }
            });

            this.cart = this.cart.filter((item) => !item.selected);
            this.persistCart();
            this.checkoutStep = false;
            this.invalidateShippingRates();
            this.filterWatches();
        },

        resetComplaintForm() {
            this.complaintForm = {
                customer_name: '',
                email: '',
                order_reference: '',
                message: '',
            };
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
