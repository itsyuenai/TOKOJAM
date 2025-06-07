<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="font-bold text-lg text-gray-800 mb-4">Filter Produk</h3>

    {{-- Filter Kategori --}}
    <div class="mb-6">
        <label for="filter-category" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
        <select
            wire:model.live="selectedCategory"
            id="filter-category"
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-base"
        >
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Filter Rentang Harga --}}
    <div class="mb-4">
        <label for="filter-priceRange" class="block text-sm font-medium text-gray-700 mb-2">Rentang Harga</label>
        <select
            wire:model.live="selectedPriceRange"
            id="filter-priceRange"
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-base"
        >
            <option value="">Semua Harga</option>
            <option value="under-2m">Di bawah Rp 2 Juta</option>
            <option value="2m-5m">Rp 2 Juta - Rp 5 Juta</option>
            <option value="above-5m">Di atas Rp 5 Juta</option>
        </select>
    </div>

    {{-- Button Reset Filters (Opsional) --}}
    {{-- <button wire:click="resetFilters" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition-colors">
        Reset Filter
    </button> --}}
</div>