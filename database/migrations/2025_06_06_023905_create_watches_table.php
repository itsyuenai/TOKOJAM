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
            $table->string('name')->unique();
            $table->foreignId('category_id')->nullable()->constrained('watch_categories')->onDelete('set null');
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('image')->nullable(); // For file uploads
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