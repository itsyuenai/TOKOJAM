<main class="watch-page" x-data="watchStore(@js($watches))" x-init="init()">
    <nav class="watch-nav">
        <div class="watch-nav-inner">
            <a href="{{ url('/') }}" class="watch-heading text-2xl font-semibold text-neutral-950">
                ID Watch
            </a>

            <div class="hidden items-center gap-8 text-sm font-medium text-neutral-600 md:flex">
                <a href="#collection" class="hover:text-neutral-950">Collection</a>
                <a href="#journal" class="hover:text-neutral-950">Journal</a>
                <a href="#contact" class="hover:text-neutral-950">Contact</a>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="watch-icon-button" @click="showFavorites = true" title="Favorites">
                    <x-heroicon-o-heart class="h-5 w-5" />
                    <span x-cloak x-show="favorites.length > 0" class="watch-badge" x-text="favorites.length"></span>
                </button>
                <button type="button" class="watch-icon-button" @click="showCart = true" title="Cart">
                    <x-heroicon-o-shopping-bag class="h-5 w-5" />
                    <span x-cloak x-show="cartItemCount > 0" class="watch-badge" x-text="cartItemCount"></span>
                </button>
            </div>
        </div>
    </nav>

    <section class="watch-hero">
        <div class="watch-container">
            <div class="watch-hero-content">
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-stone-200">Curated mechanical and classic watches</p>
                <h1 class="watch-heading mb-5 text-4xl font-semibold leading-tight sm:text-6xl">
                    ID Watch
                </h1>
                <p class="max-w-2xl text-base leading-7 text-stone-100 sm:text-lg">
                    A restrained collection of Seiko, Orient, Omega, Tissot, and daily-wear icons selected for timeless proportion and lasting presence.
                </p>
                <a href="#collection" class="watch-primary-button mt-8">
                    View Collection
                    <x-heroicon-o-arrow-down class="h-5 w-5" />
                </a>
            </div>
        </div>
    </section>

    <section id="collection" class="watch-container py-14 sm:py-20">
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Premium Collection</p>
                <h2 class="watch-heading text-3xl font-semibold text-neutral-950 sm:text-4xl">Watches In Stock</h2>
                <p class="mt-2 text-sm text-stone-600" x-text="`${filteredWatches.length} watches available`"></p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[620px]">
                <select x-model="selectedCategory" @change="filterWatches()" class="watch-control">
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category.value">
                        <option :value="category.value" x-text="category.label"></option>
                    </template>
                </select>

                <select x-model="priceRange" @change="filterWatches()" class="watch-control">
                    <option value="">All Prices</option>
                    <option value="under-2m">Under Rp 2.000.000</option>
                    <option value="2m-5m">Rp 2.000.000 - Rp 5.000.000</option>
                    <option value="5m-20m">Rp 5.000.000 - Rp 20.000.000</option>
                    <option value="above-20m">Above Rp 20.000.000</option>
                </select>

                <select x-model="sortBy" @change="sortWatches()" class="watch-control">
                    <option value="name-asc">Name A-Z</option>
                    <option value="name-desc">Name Z-A</option>
                    <option value="price-asc">Price Low-High</option>
                    <option value="price-desc">Price High-Low</option>
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
                            class="watch-icon-button absolute right-4 top-4 bg-white/90"
                            :class="isFavorite(watch.id) ? 'text-red-700' : ''"
                            @click="toggleFavorite(watch)"
                            title="Favorite"
                        >
                            <x-heroicon-o-heart class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="p-5">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="watch-heading text-xl font-semibold text-neutral-950" x-text="watch.name"></h3>
                                <p class="mt-1 text-sm text-stone-500" x-text="watch.sku || 'Curated timepiece'"></p>
                            </div>
                            <p class="whitespace-nowrap text-sm font-semibold" :class="watch.stock > 0 ? 'text-emerald-700' : 'text-red-700'">
                                <span x-text="watch.stock"></span> stock
                            </p>
                        </div>

                        <p class="mb-5 line-clamp-2 min-h-[2.75rem] text-sm leading-6 text-stone-600" x-text="watch.description || 'A refined piece selected for daily wear and long-term collecting.'"></p>

                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-neutral-950" x-text="formatPrice(watch.price)"></p>
                            <button
                                type="button"
                                class="watch-primary-button px-4"
                                @click="addToCart(watch)"
                                :disabled="watch.stock <= 0"
                            >
                                <x-heroicon-o-shopping-bag class="h-5 w-5" />
                                <span x-text="watch.stock <= 0 ? 'Sold Out' : (isInCart(watch.id) ? 'Added' : 'Add')"></span>
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <div x-cloak x-show="showCart || showFavorites" class="fixed inset-0 z-[60] bg-neutral-950/45" @click="showCart = false; showFavorites = false"></div>

    <aside
        x-cloak
        x-show="showCart"
        x-transition
        class="watch-drawer fixed right-0 top-0 z-[70] flex h-full flex-col shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-stone-200 p-5">
            <h2 class="watch-heading text-2xl font-semibold">Cart</h2>
            <button type="button" class="watch-icon-button" @click="showCart = false" title="Close cart">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5">
            <div x-show="cart.length === 0" class="py-16 text-center text-stone-500">
                <x-heroicon-o-shopping-bag class="mx-auto mb-4 h-10 w-10" />
                <p>Your cart is empty.</p>
            </div>

            <div class="space-y-4">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex gap-4 border-b border-stone-200 pb-4">
                        <img :src="item.image" :alt="item.name" class="h-20 w-20 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-neutral-950" x-text="item.name"></h3>
                            <p class="mt-1 text-sm text-stone-500" x-text="formatPrice(item.price)"></p>

                            <div class="mt-3 flex items-center gap-2">
                                <button type="button" class="watch-icon-button h-8 w-8" @click="decrementQuantity(item.id)" title="Decrease quantity">
                                    <x-heroicon-o-minus class="h-4 w-4" />
                                </button>
                                <span class="w-8 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                <button type="button" class="watch-icon-button h-8 w-8" @click="incrementQuantity(item.id)" title="Increase quantity">
                                    <x-heroicon-o-plus class="h-4 w-4" />
                                </button>
                                <button type="button" class="ml-auto text-sm font-semibold text-red-700 hover:text-red-900" @click="removeFromCart(item.id)">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="cart.length > 0" class="border-t border-stone-200 p-5">
            <label for="customer_name" class="mb-2 block text-sm font-medium text-stone-700">Customer Name</label>
            <input id="customer_name" type="text" x-model="customerName" class="watch-control mb-4 w-full" placeholder="Guest">

            <p x-cloak x-show="checkoutError" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" x-text="checkoutError"></p>

            <div class="mb-4 flex items-center justify-between text-lg font-semibold">
                <span>Total</span>
                <span x-text="formatPrice(cartTotal)"></span>
            </div>

            <button type="button" class="watch-primary-button w-full" @click="checkout()" :disabled="isCheckingOut">
                <x-heroicon-o-credit-card class="h-5 w-5" />
                <span x-text="isCheckingOut ? 'Processing...' : 'Checkout'"></span>
            </button>
        </div>
    </aside>

    <aside
        x-cloak
        x-show="showFavorites"
        x-transition
        class="watch-drawer fixed right-0 top-0 z-[70] flex h-full flex-col shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-stone-200 p-5">
            <h2 class="watch-heading text-2xl font-semibold">Favorites</h2>
            <button type="button" class="watch-icon-button" @click="showFavorites = false" title="Close favorites">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5">
            <div x-show="favorites.length === 0" class="py-16 text-center text-stone-500">
                <x-heroicon-o-heart class="mx-auto mb-4 h-10 w-10" />
                <p>No favorites yet.</p>
            </div>

            <div class="space-y-4">
                <template x-for="item in favorites" :key="item.id">
                    <div class="flex gap-4 border-b border-stone-200 pb-4">
                        <img :src="item.image" :alt="item.name" class="h-20 w-20 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-neutral-950" x-text="item.name"></h3>
                            <p class="mt-1 text-sm text-stone-500" x-text="formatPrice(item.price)"></p>
                            <button type="button" class="watch-secondary-button mt-3 min-h-9 px-3 py-1 text-sm" @click="addToCart(item)">
                                Add To Cart
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </aside>

    <div
        x-cloak
        x-show="notification.show"
        x-transition
        class="watch-notification"
        :class="notification.type"
        x-text="notification.message"
    ></div>
</main>
