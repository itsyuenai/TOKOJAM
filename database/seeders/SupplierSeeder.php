<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT. Sentosa Abadi Jaya',
                'contact_person' => 'Budi Santoso',
                'phone_number' => '081234567890',
                'email' => 'info@sentosaabadi.com',
                'address' => 'Jl. Merdeka No. 10, Jakarta Pusat, DKI Jakarta',
            ],
            [
                'name' => 'CV. Jaya Bersama',
                'contact_person' => 'Siti Aminah',
                'phone_number' => '087654321098',
                'email' => 'jayamaju@email.com',
                'address' => 'Jl. Raya Bogor KM 20, Depok, Jawa Barat',
            ],
            [
                'name' => 'UD. Maju Makmur',
                'contact_person' => 'Dwi Lestari',
                'phone_number' => '082112233445',
                'email' => 'ud_maju.makmur@gmail.com',
                'address' => 'Jl. Pahlawan No. 5, Surabaya, Jawa Timur',
            ],
            [
                'name' => 'PT. Global Indah',
                'contact_person' => 'Ahmad Fauzi',
                'phone_number' => '085000111222',
                'email' => 'admin@globalindah.co.id',
                'address' => 'Jl. Asia Afrika No. 100, Bandung, Jawa Barat',
            ],
            [
                'name' => 'Karya Mandiri Sejahtera',
                'contact_person' => 'Rina Wijaya',
                'phone_number' => '083899887766',
                'email' => 'karyamandiri@example.net',
                'address' => 'Jl. Gatot Subroto No. 20, Semarang, Jawa Tengah',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}