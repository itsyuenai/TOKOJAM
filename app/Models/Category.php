<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // Import Str facade untuk slug

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', // Pastikan slug ada di fillable
        'description',
    ];

    // Mengatasi pembuatan slug secara otomatis sebelum menyimpan model
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            // Hanya perbarui slug jika nama kategori berubah
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the watches for the category.
     */
    public function watches(): HasMany
    {
        return $this->hasMany(Watch::class);
    }
}