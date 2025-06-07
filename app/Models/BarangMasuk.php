<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk'; // Nama tabel kustom

    protected $fillable = [
        'jam_id',
        'quantity',
        'supplier',
        'purchase_price',
        'entry_date',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    /**
     * Get the watch that the incoming goods belongs to.
     */
    public function jam(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}