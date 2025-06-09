<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Watch extends Model
{
    use HasFactory;

    protected $table = 'watches'; // Pastikan nama tabel benar

   protected $guarded = [];

    /**
     * Get the category that the watch belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WatchCategory::class);
    }

    /**
     * Get the incoming goods for the watch.
     */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'jam_id'); // Tetap pakai jam_id di foreign key
    }

    /**
     * Get the outgoing goods for the watch.
     */
    public function barangKeluar(): HasMany
    {
        return $this->hasMany(BarangKeluar::class, 'jam_id'); // Tetap pakai jam_id di foreign key
    }

    /**
     * Get the order items for the watch.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'jam_id'); // Tetap pakai jam_id di foreign key
    }
}