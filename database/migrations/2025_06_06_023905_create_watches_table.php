<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah nama tabel dari 'jams' menjadi 'watches' jika sudah ada
        // Jika Anda belum menjalankan `php artisan migrate` sebelumnya, Anda bisa menghapus bagian ini
        // dan langsung menggunakan Schema::create('watches', ...)
        if (Schema::hasTable('jams')) {
            Schema::rename('jams', 'watches');
        }

        // Pastikan tabel 'watches' ada dan tambahkan/modifikasi kolom
        // Jika Anda baru pertama kali menjalankan migrasi, ini akan membuat tabel baru
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Pastikan kolom price memiliki presisi yang cukup, seperti 12,2
            $table->decimal('price', 12, 2);
            $table->string('image')->nullable(); // Tambahkan kolom image, bisa null jika tidak wajib
            // Foreign key ke tabel categories
            $table->foreignId('category_id')->constrained('watch_categories')->onDelete('restrict'); // Foreign key ke tabel watch_categories
            $table->decimal('rating', 2, 1)->default(0); // Rating dengan 1 desimal, default 0
            $table->integer('reviews_count')->default(0); // Jumlah review, default 0
            $table->text('description')->nullable();
            $table->integer('stock')->default(0); // Pertahankan kolom stock
            $table->string('image_url')->nullable(); // Tambahkan kolom image_url, bisa null jika tidak wajib
            $table->string('sku')->unique()->nullable(); // Pertahankan kolom SKU, buat nullable jika tidak selalu ada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watches');
        // Jika Anda menggunakan Schema::rename di up(), mungkin Anda ingin mengembalikan namanya di down()
        // if (Schema::hasTable('watches')) {
        //     Schema::rename('watches', 'jams');
        // }
    }
};