<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\MidtransService;
use App\Services\OnlineCheckoutService;
use App\Services\SalesLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MidtransNotificationController extends Controller
{
    public function __invoke(
        Request $request,
        MidtransService $midtrans,
        OnlineCheckoutService $checkout,
        BiteshipService $biteship,
        SalesLedgerService $salesLedger,
    ): JsonResponse {
        $notification = $request->all();

        if (! $midtrans->verifyNotification($notification)) {
            return response()->json(['message' => 'Signature pembayaran tidak valid.'], Response::HTTP_UNAUTHORIZED);
        }

        $order = Order::query()
            ->where('payment_reference', $notification['order_id'])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if (! is_numeric($notification['gross_amount'] ?? null)
            || (int) round((float) $notification['gross_amount']) !== (int) round((float) $order->total_amount)) {
            return response()->json(['message' => 'Nominal pembayaran tidak sesuai.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transactionStatus = $notification['transaction_status'] ?? null;
        $releaseStock = false;
        $paid = false;

        DB::transaction(function () use ($order, $notification, $transactionStatus, $salesLedger, &$releaseStock, &$paid): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_status === 'paid') {
                return;
            }

            $fraudStatus = $notification['fraud_status'] ?? null;

            if ($transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                $lockedOrder->forceFill([
                    'payment_status' => 'paid',
                    'payment_method' => $notification['payment_type'] ?? 'midtrans_snap',
                    'status' => 'processing',
                ])->save();
                $salesLedger->recordPaidOrder($lockedOrder);
                $paid = true;

                return;
            }

            if (in_array($transactionStatus, ['expire', 'cancel', 'deny'], true)) {
                $lockedOrder->forceFill([
                    'payment_status' => $transactionStatus,
                    'status' => 'cancelled',
                ])->save();
                $releaseStock = true;
            } elseif ($transactionStatus === 'pending') {
                $lockedOrder->forceFill(['payment_status' => 'pending'])->save();
            }
        });

        if ($releaseStock) {
            $checkout->releaseStock($order->id);
        }

        if ($paid) {
            try {
                $biteship->createShipment($order->fresh());
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return response()->json(['message' => 'Notifikasi pembayaran diproses.']);
    }
}
