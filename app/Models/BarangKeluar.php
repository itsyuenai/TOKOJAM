<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar'; // Nama tabel kustom

    protected $fillable = [
        'jam_id',
        'quantity',
        'customer_name',
        'sale_price',
        'exit_date',
    ];

    protected $casts = [
        'exit_date' => 'date',
    ];

    /**
     * Get the watch that the outgoing goods belongs to.
     */
    public function jam(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
