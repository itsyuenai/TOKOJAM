<main class="watch-page" x-data="watchStore(@js($watches))" x-init="init()">
    <nav class="watch-nav">
        <div class="watch-nav-inner">
            <a href="{{ url('/') }}" class="watch-heading text-2xl font-semibold text-neutral-950">
                ID Watch
            </a>

            <div class="watch-nav-links hidden items-center gap-6 text-sm font-medium text-neutral-600 lg:flex">
                <a href="#collection" class="hover:text-neutral-950">Katalog</a>
                <a href="#location" class="hover:text-neutral-950">Lokasi</a>
                <a href="#support" class="hover:text-neutral-950">Bantuan</a>
                <a href="#journal" class="hover:text-neutral-950">Artikel</a>
                <a href="#contact" class="hover:text-neutral-950">Kontak</a>
            </div>

            <div class="flex shrink-0 items-center gap-3">
                <button type="button" class="watch-icon-button shrink-0" @click="showFavorites = true" title="Favorit" aria-label="Buka barang favorit">
                    <x-heroicon-o-heart class="h-5 w-5" />
                    <span x-cloak x-show="favorites.length > 0" class="watch-badge" x-text="favorites.length"></span>
                </button>
                <button type="button" class="watch-icon-button shrink-0" @click="showCart = true" title="Keranjang" aria-label="Buka keranjang belanja">
                    <x-heroicon-o-shopping-bag class="h-5 w-5" />
                    <span x-cloak x-show="cartItemCount > 0" class="watch-badge" x-text="cartItemCount"></span>
                </button>
            </div>
        </div>
    </nav>

    <section class="watch-hero">
        <div class="watch-container">
            <div class="watch-hero-content">
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-stone-200">Jam tangan pilihan untuk setiap momen</p>
                <h1 class="watch-heading mb-5 text-4xl font-semibold leading-tight sm:text-6xl">
                    ID Watch
                </h1>
                <p class="max-w-2xl text-base leading-7 text-stone-100 sm:text-lg">
                    Temukan koleksi jam tangan pilihan dengan desain berkelas, kualitas terpercaya, dan layanan belanja yang mudah.
                </p>
                <a href="#collection" class="watch-primary-button mt-8">
                    Lihat Koleksi
                    <x-heroicon-o-arrow-down class="h-5 w-5" />
                </a>
            </div>
        </div>
    </section>

    <section id="collection" class="watch-container py-14 sm:py-20">
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Koleksi Pilihan</p>
                <h2 class="watch-heading text-3xl font-semibold text-neutral-950 sm:text-4xl">Jam Tangan Tersedia</h2>
                <p class="mt-2 text-sm text-stone-600" x-text="`${filteredWatches.length} jam tersedia`"></p>
            </div>

            <div class="grid min-w-0 gap-3 sm:grid-cols-2 lg:min-w-[620px] lg:grid-cols-3">
                <select x-model="selectedCategory" @change="filterWatches()" class="watch-control">
                    <option value="">Semua Kategori</option>
                    <template x-for="category in categories" :key="category.value">
                        <option :value="category.value" x-text="category.label"></option>
                    </template>
                </select>

                <select x-model="priceRange" @change="filterWatches()" class="watch-control">
                    <option value="">Semua Harga</option>
                    <option value="under-2m">Di bawah Rp 2.000.000</option>
                    <option value="2m-5m">Rp 2.000.000 - Rp 5.000.000</option>
                    <option value="5m-20m">Rp 5.000.000 - Rp 20.000.000</option>
                    <option value="above-20m">Di atas Rp 20.000.000</option>
                </select>

                <select x-model="sortBy" @change="sortWatches()" class="watch-control">
                    <option value="name-asc">Nama A-Z</option>
                    <option value="name-desc">Nama Z-A</option>
                    <option value="price-asc">Harga Terendah</option>
                    <option value="price-desc">Harga Tertinggi</option>
                </select>
            </div>
        </div>

        <div class="watch-product-grid">
            <template x-for="watch in filteredWatches" :key="watch.id">
                <article class="watch-card">
                    <div class="relative">
                        <template x-if="watch.image">
                            <img :src="watch.image" :alt="watch.name" class="watch-card-image">
                        </template>
                        <template x-if="!watch.image">
                            <div class="watch-card-image flex items-center justify-center text-stone-400">
                                <x-heroicon-o-clock class="h-12 w-12" />
                            </div>
                        </template>

                        <div class="absolute left-4 top-4">
                            <span class="watch-pill" x-text="watch.categoryName"></span>
                        </div>
                        <button
                            type="button"
                            class="watch-icon-button watch-card-favorite bg-white/90"
                            :class="isFavorite(watch.id) ? 'text-red-700' : ''"
                            @click="toggleFavorite(watch)"
                            title="Favorit"
                            :aria-label="isFavorite(watch.id) ? `Hapus ${watch.name} dari favorit` : `Tambahkan ${watch.name} ke favorit`"
                        >
                            <x-heroicon-o-heart class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="p-5">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="watch-heading text-xl font-semibold text-neutral-950" x-text="watch.name"></h3>
                                <p class="mt-1 text-sm text-stone-500" x-text="watch.sku || 'Curated timepiece'"></p>
                            </div>
                            <p class="shrink-0 whitespace-nowrap text-xs font-semibold sm:text-sm" :class="watch.stock > 0 ? 'text-emerald-700' : 'text-red-700'">
                                <span x-text="watch.stock"></span> tersedia
                            </p>
                        </div>

                        <p class="mb-5 line-clamp-2 min-h-[2.75rem] text-sm leading-6 text-stone-600" x-text="watch.description || 'A refined piece selected for daily wear and long-term collecting.'"></p>

                        <div class="watch-card-purchase">
                            <p class="font-semibold text-neutral-950" x-text="formatPrice(watch.price)"></p>
                            <button
                                type="button"
                                class="watch-primary-button w-full px-4"
                                @click="addToCart(watch)"
                                :disabled="watch.stock <= 0"
                            >
                                <x-heroicon-o-shopping-bag class="h-5 w-5" />
                                <span x-text="watch.stock <= 0 ? 'Habis' : (isInCart(watch.id) ? 'Ditambahkan' : 'Tambah')"></span>
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <section id="location" class="watch-location-section">
        <div class="watch-container py-14 sm:py-20">
            <div class="mb-8 grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
                <div>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Toko Fisik</p>
                    <h2 class="watch-heading text-3xl font-semibold text-neutral-950 sm:text-4xl">Kunjungi Toko Jam</h2>
                </div>
                <p class="max-w-2xl text-sm leading-6 text-stone-600 lg:ml-auto">
                    Kunjungi toko kami untuk konsultasi, mencoba jam secara langsung, dan mendapatkan bantuan setelah pembelian.
                </p>
            </div>

            <div class="watch-location-grid">
                <div class="watch-location-details">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-stone-500">Alamat</p>
                        <p class="watch-heading mt-3 text-2xl font-semibold text-neutral-950">Toko Jam Citeureup</p>
                        <p class="mt-2 text-sm leading-6 text-stone-600">
                            Citeureup, Kabupaten Bogor, Jawa Barat 16810
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-neutral-950">Jam Operasional</p>
                            <p class="mt-1 text-sm text-stone-600">Senin-Sabtu, 10.00-20.00</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-neutral-950">Kontak</p>
                            <p class="mt-1 text-sm text-stone-600">support@tokojam.test</p>
                        </div>
                    </div>

                    <a
                        href="https://www.openstreetmap.org/?mlat=-6.4854&mlon=106.8810#map=16/-6.4854/106.8810"
                        target="_blank"
                        rel="noopener"
                        class="watch-secondary-button w-full sm:w-auto"
                    >
                        <x-heroicon-o-map-pin class="h-5 w-5" />
                        Buka Peta
                    </a>
                </div>

                <div class="watch-map-frame">
                    <iframe
                        id="store-map"
                        class="watch-map"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=106.8610%2C-6.4954%2C106.9010%2C-6.4754&layer=mapnik&marker=-6.4854%2C106.8810"
                        title="Peta lokasi Toko Jam di Citeureup, Kabupaten Bogor"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
    </section>

    <section id="support" class="watch-container py-14 sm:py-20">
        <div class="watch-support-grid">
            <div class="watch-support-copy">
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Layanan Pelanggan</p>
                <h2 class="watch-heading text-3xl font-semibold text-neutral-950 sm:text-4xl">Ada yang Bisa Kami Bantu?</h2>
                <p class="mt-4 text-sm leading-7 text-stone-600">
                    Sampaikan kendala pesanan, pertanyaan garansi, masalah produk, atau keluhan pengiriman. Pesan Anda tercatat agar dapat ditindaklanjuti oleh admin.
                </p>
                <div class="mt-8 space-y-4 border-l border-stone-300 pl-5 text-sm text-stone-600">
                    <p><span class="font-semibold text-neutral-950">Waktu respons:</span> maksimal 1 hari kerja</p>
                    <p><span class="font-semibold text-neutral-950">Tips:</span> sertakan nomor pesanan jika tersedia</p>
                </div>
            </div>

            <form class="watch-support-form" @submit.prevent="submitComplaint()">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-stone-700">Nama</span>
                        <input type="text" x-model="complaintForm.customer_name" class="watch-control w-full" required maxlength="255">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-stone-700">Email</span>
                        <input type="email" x-model="complaintForm.email" class="watch-control w-full" required maxlength="255">
                    </label>
                </div>

                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-stone-700">Nomor Pesanan</span>
                    <input type="text" x-model="complaintForm.order_reference" class="watch-control w-full" maxlength="255" placeholder="Opsional">
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-stone-700">Pesan</span>
                    <textarea x-model="complaintForm.message" class="watch-control min-h-36 w-full resize-y" required minlength="10" maxlength="5000"></textarea>
                </label>

                <p x-cloak x-show="complaintError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" x-text="complaintError"></p>
                <p x-cloak x-show="complaintSuccess" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" x-text="complaintSuccess"></p>

                <button type="submit" class="watch-primary-button w-full sm:w-auto" :disabled="isSubmittingComplaint">
                    <x-heroicon-o-paper-airplane class="h-5 w-5" />
                    <span x-text="isSubmittingComplaint ? 'Mengirim...' : 'Kirim Keluhan'"></span>
                </button>
            </form>
        </div>
    </section>

    <div x-cloak x-show="showCart || showFavorites" class="fixed inset-0 z-[60] bg-neutral-950/45" @click="showCart = false; showFavorites = false"></div>

    <aside
        x-cloak
        x-show="showCart"
        x-transition
        class="watch-drawer fixed right-0 top-0 z-[70] flex h-full flex-col shadow-2xl"
    >
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 p-4 sm:p-5">
            <div>
                <h2 class="watch-heading text-2xl font-semibold" x-text="checkoutStep ? 'Checkout' : 'Keranjang'"></h2>
                <p class="mt-1 text-xs text-stone-500" x-text="checkoutStep ? 'Langkah 2 dari 2 · Alamat & pengiriman' : 'Langkah 1 dari 2 · Pilih barang'"></p>
            </div>
            <button type="button" class="watch-icon-button shrink-0" @click="showCart = false" title="Tutup keranjang" aria-label="Tutup keranjang">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
            <div x-show="cart.length === 0" class="py-16 text-center text-stone-500">
                <x-heroicon-o-shopping-bag class="mx-auto mb-4 h-10 w-10" />
                <p>Keranjang masih kosong.</p>
            </div>

            <div x-show="cart.length > 0 && !checkoutStep" class="space-y-4">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex gap-3 border-b border-stone-200 pb-4">
                        <input
                            type="checkbox"
                            x-model="item.selected"
                            @change="cartSelectionChanged()"
                            :aria-label="`Pilih ${item.name} untuk checkout`"
                            class="mt-1 h-5 w-5 shrink-0 rounded border-stone-300 text-neutral-900 focus:ring-neutral-800"
                        >
                        <img :src="item.image" :alt="item.name" class="h-16 w-16 shrink-0 rounded-lg object-cover sm:h-20 sm:w-20">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-neutral-950" x-text="item.name"></h3>
                            <p class="mt-1 text-sm text-stone-500" x-text="`${formatPrice(item.price)} / buah`"></p>
                            <p class="mt-1 text-sm font-semibold text-neutral-900" x-text="formatPrice(item.price * item.quantity)"></p>

                            <div class="mt-3 flex items-center gap-2">
                                <button type="button" class="watch-icon-button h-8 w-8" @click="decrementQuantity(item.id)" title="Decrease quantity">
                                    <x-heroicon-o-minus class="h-4 w-4" />
                                </button>
                                <span class="w-8 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                <button type="button" class="watch-icon-button h-8 w-8" @click="incrementQuantity(item.id)" title="Increase quantity">
                                    <x-heroicon-o-plus class="h-4 w-4" />
                                </button>
                                <button type="button" class="ml-auto min-h-9 px-2 text-sm font-semibold text-red-700 hover:text-red-900" @click="removeFromCart(item.id)">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <section x-show="checkoutStep" class="space-y-5">
                <div>
                    <h3 class="font-semibold text-neutral-950">Barang yang akan dibeli</h3>
                    <div class="mt-3 space-y-3">
                        <template x-for="item in selectedItems" :key="`checkout-${item.id}`">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0" x-text="`${item.name} × ${item.quantity}`"></span>
                                <span class="shrink-0 font-medium" x-text="formatPrice(item.price * item.quantity)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-t border-stone-200 pt-4">
                    <h3 class="mb-3 font-semibold text-neutral-950">Alamat penerima</h3>
                    <label for="customer_name" class="mb-3 block text-sm font-medium text-stone-700">
                        Nama lengkap
                        <input id="customer_name" type="text" x-model="customerName" class="watch-control mt-1 w-full" required maxlength="255" autocomplete="name">
                    </label>
                    <label class="mb-3 block text-sm font-medium text-stone-700">
                        Email
                        <input type="email" x-model="customerEmail" class="watch-control mt-1 w-full" required maxlength="255" autocomplete="email">
                    </label>
                    <label class="mb-3 block text-sm font-medium text-stone-700">
                        Nomor WhatsApp
                        <input type="tel" x-model="customerPhone" class="watch-control mt-1 w-full" required maxlength="30" autocomplete="tel">
                    </label>
                    <label class="mb-3 block text-sm font-medium text-stone-700">
                        Alamat lengkap
                        <textarea x-model="shippingAddress" class="watch-control mt-1 w-full" required maxlength="2000" rows="3" autocomplete="street-address"></textarea>
                    </label>
                    <label class="mb-2 block text-sm font-medium text-stone-700">
                        Kode pos tujuan
                        <input type="text" inputmode="numeric" x-model="shippingPostalCode" @input="invalidateShippingRates()" class="watch-control mt-1 w-full" required maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code">
                    </label>
                    <button type="button" class="watch-secondary-button w-full" @click="fetchShippingRates()" :disabled="isLoadingShippingRates || selectedItems.length === 0">
                        <span x-text="isLoadingShippingRates ? 'Memuat ongkir...' : 'Cek ongkir kurir'"></span>
                    </button>
                </div>

                <div x-cloak x-show="shippingRates.length > 0" class="space-y-2">
                    <p class="text-sm font-semibold text-stone-700">Pilih layanan pengiriman</p>
                    <template x-for="rate in shippingRates" :key="`${rate.courier_company}:${rate.courier_type}`">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-stone-200 p-3 text-sm">
                            <input type="radio" name="shipping_rate" x-model="selectedShippingRate" :value="`${rate.courier_company}:${rate.courier_type}`">
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold" x-text="`${rate.courier_name} · ${rate.courier_type}`"></span>
                                <span class="text-stone-500" x-text="rate.duration"></span>
                            </span>
                            <span class="font-semibold" x-text="formatPrice(rate.price)"></span>
                        </label>
                    </template>
                </div>

                <div x-cloak x-show="checkoutError" class="space-y-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <p x-text="checkoutError"></p>
                    <button x-cloak x-show="csrfMismatch" type="button" class="watch-secondary-button w-full" @click="window.location.reload()">
                        Muat ulang halaman
                    </button>
                </div>

                <div class="space-y-2 border-t border-stone-200 pt-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal barang</span>
                        <span x-text="formatPrice(selectedCartTotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Ongkos kirim</span>
                        <span x-text="selectedShipping ? formatPrice(selectedShipping.price) : 'Pilih kurir untuk menghitung'"></span>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-3 text-lg font-semibold">
                        <span>Total pembayaran</span>
                        <span x-text="formatPrice(selectedCartTotal + (selectedShipping?.price || 0))"></span>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="cart.length > 0" class="border-t border-stone-200 p-4 sm:p-5">
            <div x-show="!checkoutStep">
                <p x-cloak x-show="selectedItems.length === 0" class="mb-3 text-sm text-stone-500">
                    Pilih barang yang ingin dibeli dengan mencentang kotaknya.
                </p>
                <div class="mb-4 flex justify-between text-sm">
                    <span><span x-text="selectedCartItemCount"></span> barang dipilih</span>
                    <span class="font-semibold" x-text="formatPrice(selectedCartTotal)"></span>
                </div>
                <p x-cloak x-show="checkoutError" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" x-text="checkoutError"></p>
                <button x-cloak x-show="csrfMismatch" type="button" class="watch-secondary-button mb-4 w-full" @click="window.location.reload()">
                    Muat ulang halaman
                </button>
                <button type="button" class="watch-primary-button w-full" @click="checkout()" :disabled="selectedItems.length === 0">
                    <x-heroicon-o-shopping-bag class="h-5 w-5" />
                    <span>Checkout barang dipilih</span>
                </button>
            </div>

            <div x-show="checkoutStep" class="flex gap-3">
                <button type="button" class="watch-secondary-button flex-1" @click="backToCart()" :disabled="isCheckingOut">
                    Kembali
                </button>
                <button type="button" class="watch-primary-button flex-1" @click="placeOrder()" :disabled="isCheckingOut || !selectedShipping">
                    <x-heroicon-o-credit-card class="h-5 w-5" />
                    <span x-text="isCheckingOut ? 'Memproses...' : 'Lanjut bayar'"></span>
                </button>
            </div>
        </div>
    </aside>

    <aside
        x-cloak
        x-show="showFavorites"
        x-transition
        class="watch-drawer fixed right-0 top-0 z-[70] flex h-full flex-col shadow-2xl"
    >
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 p-4 sm:p-5">
            <h2 class="watch-heading text-2xl font-semibold">Favorit</h2>
            <button type="button" class="watch-icon-button shrink-0" @click="showFavorites = false" title="Tutup favorit" aria-label="Tutup favorit">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
            <div x-show="favorites.length === 0" class="py-16 text-center text-stone-500">
                <x-heroicon-o-heart class="mx-auto mb-4 h-10 w-10" />
                <p>Belum ada barang favorit.</p>
            </div>

            <div class="space-y-4">
                <template x-for="item in favorites" :key="item.id">
                    <div class="flex gap-4 border-b border-stone-200 pb-4">
                        <img :src="item.image" :alt="item.name" class="h-20 w-20 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-neutral-950" x-text="item.name"></h3>
                            <p class="mt-1 text-sm text-stone-500" x-text="formatPrice(item.price)"></p>
                            <button type="button" class="watch-secondary-button mt-3 min-h-9 px-3 py-1 text-sm" @click="addToCart(item)">
                                Tambahkan ke Keranjang
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </aside>

    <section class="watch-helpdesk" aria-label="Asisten bantuan pelanggan">
        <div
            x-cloak
            x-show="showHelpdesk"
            class="watch-helpdesk-panel"
            role="dialog"
            aria-modal="false"
            aria-labelledby="helpdesk-title"
        >
            <header class="watch-helpdesk-header">
                <div class="min-w-0">
                    <h2 id="helpdesk-title" class="watch-heading text-lg font-semibold">Asisten TOKOJAM</h2>
                    <p class="mt-1 text-xs text-stone-200">Bantuan produk dan cara berbelanja</p>
                </div>
                <button type="button" class="watch-icon-button shrink-0" @click="showHelpdesk = false" aria-label="Tutup asisten bantuan">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </header>

            <div class="watch-helpdesk-messages" x-ref="helpdeskMessagesList" aria-live="polite">
                <template x-for="(message, index) in helpdeskMessages" :key="index">
                    <p class="watch-helpdesk-message" :class="message.role === 'user' ? 'watch-helpdesk-message-user' : 'watch-helpdesk-message-assistant'" x-text="message.content"></p>
                </template>
                <p x-cloak x-show="isSendingHelpdesk" class="watch-helpdesk-message watch-helpdesk-message-assistant" role="status">Asisten sedang menyiapkan jawaban...</p>
            </div>

            <form class="watch-helpdesk-form" @submit.prevent="sendHelpdeskMessage()">
                <p class="watch-helpdesk-privacy">Jangan kirim alamat, nomor telepon, OTP, kata sandi, atau data pembayaran. Pertanyaan dikirim ke OpenAI untuk dijawab.</p>
                <p x-cloak x-show="helpdeskError" class="watch-helpdesk-error" role="alert" x-text="helpdeskError"></p>
                <label for="helpdesk-question" class="sr-only">Tulis pertanyaan Anda</label>
                <textarea
                    id="helpdesk-question"
                    x-model="helpdeskQuestion"
                    @keydown.enter.prevent="sendHelpdeskMessage()"
                    class="watch-control watch-helpdesk-input"
                    rows="2"
                    maxlength="1200"
                    placeholder="Tulis pertanyaan..."
                    :disabled="isSendingHelpdesk"
                ></textarea>
                <button type="submit" class="watch-primary-button w-full" :disabled="isSendingHelpdesk || !helpdeskQuestion.trim()">
                    <x-heroicon-o-paper-airplane class="h-5 w-5" />
                    <span x-text="isSendingHelpdesk ? 'Mengirim...' : 'Kirim pertanyaan'"></span>
                </button>
            </form>
        </div>

        <button
            type="button"
            class="watch-helpdesk-toggle"
            @click="showHelpdesk = !showHelpdesk"
            :aria-expanded="showHelpdesk.toString()"
            aria-controls="helpdesk-title"
            aria-label="Buka asisten bantuan TOKOJAM"
        >
            <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
            <span x-text="showHelpdesk ? 'Tutup bantuan' : 'Butuh bantuan?'"></span>
        </button>
    </section>

    <div
        x-cloak
        x-show="notification.show"
        x-transition
        class="watch-notification"
        :class="notification.type"
        x-text="notification.message"
    ></div>
</main>
