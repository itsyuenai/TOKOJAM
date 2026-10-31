<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Watch;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiHelpdeskService
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function reply(array $messages): string
    {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('Helpdesk AI belum aktif. Admin toko perlu mengatur OPENAI_API_KEY di file .env.');
        }

        $catalog = Watch::query()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->limit(30)
            ->get(['name', 'price', 'stock', 'description'])
            ->map(static fn (Watch $watch): array => [
                'nama' => $watch->name,
                'harga_rupiah' => (int) round((float) $watch->price),
                'stok' => (int) $watch->stock,
                'deskripsi' => mb_substr((string) $watch->description, 0, 240),
            ])
            ->values()
            ->all();

        $response = Http::acceptJson()
            ->withToken($apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.3,
                'max_tokens' => 350,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten layanan pelanggan TOKOJAM. Jawab selalu dalam bahasa Indonesia yang ramah, ringkas, dan jelas. Gunakan hanya data katalog berikut untuk pertanyaan produk: '.json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'. Harga dalam rupiah. Jangan mengarang stok, diskon, garansi, kebijakan pengembalian, estimasi pengiriman, atau status pesanan. Untuk tarif pengiriman, arahkan pelanggan mengisi alamat dan memilih "Cek ongkir kurir" saat checkout. Untuk status pesanan atau bantuan yang memerlukan data pribadi, arahkan pelanggan ke halaman pelacakan pesanan atau formulir bantuan toko. Jangan meminta kata sandi, OTP, nomor kartu, atau API key. Jangan meminta pelanggan membagikan alamat, nomor telepon, atau informasi pribadi di chat ini.',
                    ],
                    ...$messages,
                ],
            ])
            ->throw()
            ->json();

        $reply = data_get($response, 'choices.0.message.content');

        if (! is_string($reply) || trim($reply) === '') {
            throw new RuntimeException('OpenAI tidak mengembalikan jawaban helpdesk yang valid.');
        }

        return trim($reply);
    }
}
