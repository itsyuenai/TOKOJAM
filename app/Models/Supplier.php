<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone_number',
        'email',
        'address',
    ];

    /**
     * Get the barang masuk records associated with the supplier.
     */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }
}