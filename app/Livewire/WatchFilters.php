<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;

class WatchFilters extends Component
{
    // Properti untuk menyimpan nilai filter lokal di komponen ini
    public string $selectedCategory = '';
    public string $selectedPriceRange = '';

    // Menggunakan properti 'parent' untuk mengakses properti dari komponen induk
    // Atau bisa juga tidak menggunakan 'parent' dan langsung dispatch event
    // Saya memilih menggunakan dispatch event agar lebih decoupled
    // public string $search = ''; // Tidak perlu karena search di handle parent

    // Metode ini dipanggil saat properti lokal berubah
    public function updated($propertyName)
    {
        // Setiap kali filter berubah, pancarkan event ke komponen induk
        if (in_array($propertyName, ['selectedCategory', 'selectedPriceRange'])) {
            $this->dispatch('filtersUpdated', $this->selectedCategory, $this->selectedPriceRange);
        }
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        return view('livewire.watch-filters', [
            'categories' => $categories,
        ]);
    }
}