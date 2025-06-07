    <section class="py-8 md:py-12 bg-white rounded-lg shadow-sm mt-8">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Produk Rekomendasi</h2>
            <p class="text-gray-600">
                (Area ini akan menampilkan rekomendasi jam tangan. Anda bisa menambahkan logika di
                `app/Livewire/WatchRecommendations.php` untuk mengambil data dan menampilkannya di sini.)
            </p>
            {{-- Placeholder untuk grid produk rekomendasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
                <!-- Contoh placeholder item -->
                <div class="bg-gray-100 rounded-lg p-4">
                    <h3 class="font-medium text-lg">Jam Rekomendasi 1</h3>
                    <p class="text-sm text-gray-600">Rp 1.500.000</p>
                </div>
                <div class="bg-gray-100 rounded-lg p-4">
                    <h3 class="font-medium text-lg">Jam Rekomendasi 2</h3>
                    <p class="text-sm text-gray-600">Rp 2.000.000</p>
                </div>
            </div>
        </div>
    </section>