<section class="py-12 md:py-16 bg-blue-700 text-white mt-8 rounded-lg shadow-md">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Dapatkan Diskon Spesial!</h2>
            <p class="text-lg mb-8">Berlangganan newsletter kami untuk penawaran eksklusif dan berita terbaru.</p>

            <form wire:submit.prevent="subscribe" class="max-w-xl mx-auto flex flex-col sm:flex-row gap-4">
                <input
                    wire:model.live="email"
                    type="email"
                    placeholder="Masukkan email Anda"
                    class="flex-grow px-6 py-3 rounded-lg border border-blue-500 text-gray-900 focus:ring-blue-300 focus:border-blue-300 text-lg"
                >
                <button
                    type="submit"
                    class="px-8 py-3 bg-white text-blue-700 font-bold rounded-lg hover:bg-gray-100 transition-colors text-lg"
                >
                    Berlangganan
                </button>
            </form>
            {{-- Display validation error for email field --}}
            @error('email') <span class="text-red-300 text-sm mt-2 block">{{ $message }}</span> @enderror
        </div>
</section>
    