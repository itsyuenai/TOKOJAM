# TOKOJAM

Aplikasi toko jam tangan berbasis Laravel, Livewire, dan Filament. Fitur yang tersedia:

- Katalog online, keranjang, checkout tamu, tarif kurir, halaman status pesanan, dan pembayaran online.
- Pembayaran hosted Midtrans Snap. QRIS, GoPay/OVO, dan virtual account bank mengikuti kanal yang diaktifkan pada akun Midtrans.
- Tarif dan pemesanan kurir melalui Biteship.
- Admin untuk katalog, kategori, stok masuk/keluar, supplier, pesanan, keluhan, dan pembuatan pengiriman.
- POS kasir tetap tersedia di `/pos`.

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`, isi `APP_KEY`, URL publik, dan koneksi database.
2. Jalankan `composer install`, `npm install`, `php artisan migrate`, dan `npm run build`.
3. Buat akun admin dengan `php artisan app:make-admin admin@tokojam.id --name="Admin Toko"`. Password dimasukkan secara interaktif dan tidak disimpan di command history.
4. Jalankan aplikasi melalui web server HTTPS. Jangan gunakan `APP_DEBUG=true` di production.

Untuk menampilkan tiga contoh produk pada database yang masih kosong, jalankan `php artisan db:seed --class=DemoWatchCatalogSeeder`. Seeder ini aman dijalankan ulang dan hanya mengelola produk dengan SKU `DEMO-*`.

## Mengaktifkan pembayaran dan pengiriman

Integrasi belum dapat memproses uang atau membuat resi sampai akun merchant disetujui dan kredensial provider tersedia. Mulai dari mode sandbox:

- Isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_SANDBOX=true`.
- Di dashboard Midtrans, daftarkan URL notifikasi `https://domain-toko.example/payments/midtrans/notification`.
- Buat/aktifkan akun merchant Biteship, lalu salin API key dari dashboard Biteship ke `BITESHIP_API_KEY` di `.env`. API key terikat ke akun toko dan tidak bisa dibuat oleh aplikasi.
- Untuk toko di Citeureup, gunakan kode pos asal `16810`. Isi nama, telepon/email kontak, dan alamat penjemputan toko yang lengkap pada variabel `BITESHIP_ORIGIN_*` di `.env`. Email kontak asal wajib diisi untuk pembuatan pengiriman.
- Atur `BITESHIP_COURIERS` sesuai layanan yang aktif di akun. Atur berat tiap jam dalam gram di Admin → Katalog Jam Tangan.
- Untuk mengaktifkan asisten AI pelanggan, isi `OPENAI_API_KEY` di `.env` dengan key akun OpenAI Anda. Model dapat diubah lewat `OPENAI_MODEL` (default `gpt-4o-mini`). Pertanyaan chat dikirim ke OpenAI, tidak disimpan oleh aplikasi, dan UI memperingatkan pelanggan agar tidak mengirim data pribadi.
- Jalankan `php artisan config:clear` setelah mengubah konfigurasi.
- Uji permintaan tarif dan pembuatan pesanan pengiriman menggunakan API key akun sebelum mengaktifkan toko untuk pelanggan.

Stok online dicadangkan saat pesanan dibuat. Status pembayaran hanya diperbarui dari notifikasi Midtrans dengan signature yang valid; pesanan yang dibatalkan/kedaluwarsa mengembalikan stok. Pengiriman otomatis dipesan setelah pembayaran terkonfirmasi. Jika API kurir sedang gagal, admin dapat mencoba lagi dari daftar pesanan.

Simpan semua key di `.env`/secret manager deployment, jangan commit key live, dan gunakan HTTPS untuk callback provider.
