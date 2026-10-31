<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

final class OrderTrackingController extends Controller
{
    public function __invoke(string $token): View
    {
        $order = Order::query()
            ->where('tracking_token', $token)
            ->with('orderItems.watch')
            ->firstOrFail();

        return view('orders.track', ['order' => $order]);
    }
}
