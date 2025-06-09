<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];



    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the watch that owns the order item.
     */
   public function watch(): BelongsTo
    {
        // Pastikan nama relasi ini sesuai dengan kolom foreign key di order_items
        // Di migrasi Anda menggunakan 'jam_id', jadi relasi ke Watch adalah 'watch'
        return $this->belongsTo(Watch::class, 'jam_id');
    }
}