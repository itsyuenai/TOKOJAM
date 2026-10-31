<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShippingRateRequest;
use App\Services\OnlineCheckoutService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ShippingRateController extends Controller
{
    public function __invoke(ShippingRateRequest $request, OnlineCheckoutService $checkout): JsonResponse
    {
        try {
            return response()->json([
                'rates' => $checkout->shippingRates(
                    $request->validated('items'),
                    $request->validated('destination_postal_code'),
                ),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => ['items' => [$exception->getMessage()]],
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Layanan kurir sedang bermasalah. Coba kembali beberapa saat lagi.',
            ], 503);
        }
    }
}
