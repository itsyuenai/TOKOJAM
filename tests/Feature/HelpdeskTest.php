<?php

namespace Tests\Feature;

use App\Models\Watch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_helpdesk_uses_openai_with_current_catalog_context(): void
    {
        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'gpt-4o-mini',
        ]);
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'Chronograph Heritage tersedia seharga Rp 3.850.000.'],
                ]],
            ]),
        ]);
        Watch::query()->create([
            'name' => 'Chronograph Heritage',
            'price' => 3850000,
            'stock' => 5,
            'weight_gram' => 500,
            'sku' => 'DEMO-CHRONO-001',
            'description' => 'Jam chronograph pilihan.',
        ]);

        $this->postJson(route('helpdesk.chat'), [
            'messages' => [
                ['role' => 'user', 'content' => 'Berapa harga Chronograph Heritage?'],
            ],
        ])->assertOk()
            ->assertJsonPath('reply', 'Chronograph Heritage tersedia seharga Rp 3.850.000.');

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-openai-key')
            && $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request->data()['model'] === 'gpt-4o-mini'
            && str_contains($request->data()['messages'][0]['content'], 'Chronograph Heritage')
            && $request->data()['messages'][1]['content'] === 'Berapa harga Chronograph Heritage?');
    }

    public function test_helpdesk_reports_missing_api_key_without_calling_openai(): void
    {
        config(['services.openai.api_key' => null]);
        Http::fake();

        $this->postJson(route('helpdesk.chat'), [
            'messages' => [
                ['role' => 'user', 'content' => 'Bagaimana cara belanja?'],
            ],
        ])->assertServiceUnavailable()
            ->assertJsonPath('message', 'Helpdesk AI belum aktif. Admin toko perlu mengatur OPENAI_API_KEY di file .env.');

        Http::assertNothingSent();
    }

    public function test_helpdesk_rejects_assistant_messages_as_the_last_message(): void
    {
        $this->postJson(route('helpdesk.chat'), [
            'messages' => [
                ['role' => 'assistant', 'content' => 'Jawaban yang dikirim dari pelanggan.'],
            ],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Kirim pertanyaan terlebih dahulu.');
    }
}
