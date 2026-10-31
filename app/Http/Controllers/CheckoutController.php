<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCheckoutRequest;
use App\Services\OnlineCheckoutService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class CheckoutController extends Controller
{
    public function __invoke(ProcessCheckoutRequest $request, OnlineCheckoutService $checkoutService): JsonResponse
    {
        $validated = $request->validated();

        try {
            $order = $checkoutService->checkout(
                items: $validated['items'],
                customer: $validated,
            );
        } catch (\App\Exceptions\InsufficientStockException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'items' => [$exception->getMessage()],
                ],
                'stock' => $exception->context(),
            ], 422);
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
                    : 'Layanan pembayaran sedang bermasalah. Coba kembali beberapa saat lagi.',
            ], 500);
        }

        return response()->json([
            'message' => "Pesanan #{$order->id} dibuat. Selesaikan pembayaran melalui Midtrans.",
            'order' => [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'payment_url' => $order->payment_url,
                'tracking_url' => route('orders.track', $order->tracking_token),
            ],
        ], 201);
    }
}
