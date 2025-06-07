<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads; // Import ini!
use App\Models\Watch;
use Illuminate\Support\Facades\Storage; // Import Storage facade

class CreateWatch extends Component
{
    use WithFileUploads; // Gunakan trait ini

    public $name;
    public $category;
    public $price;
    public $old_price;
    public $image; // Properti untuk menampung file yang diunggah

    protected $rules = [
        'name' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'price' => 'required|numeric|min:0',
        'old_price' => 'nullable|numeric|min:0|gt:price', // Harga lama harus lebih besar dari harga baru
        'image' => 'required|image|max:1024', // Validasi: harus gambar, max 1MB
    ];

    public function saveWatch()
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            // Simpan gambar ke direktori 'watches' di dalam storage/app/public
            $imagePath = $this->image->store('watches', 'public');
            // Path yang disimpan di DB adalah 'watches/nama_file_unik.jpg'
        }

        Watch::create([
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'image' => basename($imagePath), // Hanya simpan nama file, bukan path lengkap storage
                                             // Jika Anda ingin menyimpan path lengkapnya, cukup $imagePath
        ]);

        session()->flash('message', 'Watch added successfully!');
        $this->reset(['name', 'category', 'price', 'old_price', 'image']); // Reset form
    }

    public function render()
    {
        return view('livewire.admin.create-watch');
    }
}