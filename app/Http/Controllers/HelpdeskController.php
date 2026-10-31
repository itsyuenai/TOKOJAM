<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HelpdeskChatRequest;
use App\Services\OpenAiHelpdeskService;
use Illuminate\Http\JsonResponse;
use Throwable;

final class HelpdeskController extends Controller
{
    public function __invoke(HelpdeskChatRequest $request, OpenAiHelpdeskService $helpdesk): JsonResponse
    {
        $messages = $request->validated('messages');

        if ($messages[array_key_last($messages)]['role'] !== 'user') {
            return response()->json([
                'message' => 'Kirim pertanyaan terlebih dahulu.',
            ], 422);
        }

        try {
            return response()->json([
                'reply' => $helpdesk->reply($messages),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $message = str_starts_with($exception->getMessage(), 'Helpdesk AI belum aktif.')
                ? $exception->getMessage()
                : 'Asisten sedang sulit dihubungi. Silakan coba kembali atau gunakan formulir bantuan toko.';

            return response()->json(['message' => $message], 503);
        }
    }
}
