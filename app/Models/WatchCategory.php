<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional, jika Anda menggunakan Factory
use Illuminate\Database\Eloquent\Model;

class WatchCategory extends Model
{
    use HasFactory; // Opsional, jika Anda menggunakan Factory

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'watch_categories'; // Pastikan nama tabel sesuai dengan migrasi Anda

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // Tambahkan kolom lain di sini jika Anda memilikinya di tabel watch_categories
        // contoh: 'description', 'slug', dll.
    ];

    /**
     * Get the watches for the watch category.
     */
    public function watches()
    {
        return $this->hasMany(Watch::class, 'category_id');
    }
}