<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Pesanan #{{ $order->id }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-neutral-900">
    <main class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ url('/') }}" class="text-sm font-semibold text-stone-600 hover:text-neutral-950">← Kembali ke toko</a>
        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-stone-500">Pembaruan pesanan</p>
            <h1 class="mt-2 text-3xl font-semibold">Pesanan #{{ $order->id }}</h1>
            <p class="mt-2 text-stone-600">Terima kasih, {{ $order->customer_name }}.</p>

            <dl class="mt-8 grid gap-4 border-y border-stone-200 py-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-stone-500">Pembayaran</dt>
                    <dd class="mt-1 font-semibold">{{ ucfirst($order->payment_status) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">Status pesanan</dt>
                    <dd class="mt-1 font-semibold">{{ ucfirst($order->status) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">Kurir / layanan</dt>
                    <dd class="mt-1 font-semibold">
                        {{ $order->shipping_courier ? strtoupper($order->shipping_courier).' / '.$order->shipping_service : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">Nomor resi</dt>
                    <dd class="mt-1 font-semibold">{{ $order->tracking_number ?: 'Belum diterbitkan' }}</dd>
                </div>
            </dl>

            <div class="mt-6 space-y-3">
                @foreach($order->orderItems as $item)
                    <div class="flex justify-between gap-4 text-sm">
                        <span>{{ $item->watch?->name ?? 'Produk' }} × {{ $item->quantity }}</span>
                        <span>Rp {{ number_format($item->price_per_item * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-stone-200 pt-3 text-sm">
                    <span>Ongkos kirim</span>
                    <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($order->payment_status === 'pending' && $order->payment_url)
                <a href="{{ $order->payment_url }}" class="mt-8 inline-flex rounded-lg bg-neutral-900 px-5 py-3 font-semibold text-white hover:bg-neutral-700">
                    Lanjutkan pembayaran
                </a>
            @elseif($order->payment_status === 'paid')
                <p class="mt-8 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800">
                    Pembayaran telah dikonfirmasi. Kami sedang memproses pengiriman Anda.
                </p>
            @else
                <p class="mt-8 rounded-lg bg-amber-50 p-4 text-sm text-amber-900">
                    Status pembayaran: {{ ucfirst($order->payment_status) }}.
                </p>
            @endif
        </section>
    </main>
</body>
</html>
