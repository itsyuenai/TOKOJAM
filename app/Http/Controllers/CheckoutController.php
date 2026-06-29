<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\ProcessCheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Throwable;

final class CheckoutController extends Controller
{
    public function __invoke(ProcessCheckoutRequest $request, CheckoutService $checkoutService): JsonResponse
    {
        $validated = $request->validated();

        try {
            $order = $checkoutService->process(
                items: $validated['items'],
                customerName: $validated['customer_name'] ?? null,
            );
        } catch (InsufficientStockException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'items' => [$exception->getMessage()],
                ],
                'stock' => $exception->context(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Checkout gagal diproses. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'message' => "Checkout berhasil. Order #{$order->id} telah dibuat.",
            'order' => [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
            ],
        ], 201);
    }
}
