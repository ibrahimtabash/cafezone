<main class="mx-auto w-full max-w-3xl px-4 py-10" wire:poll.30s>
    <h1 class="text-3xl font-bold text-primary">طلباتي</h1>
    <p class="mt-3 text-sm text-muted-foreground">طلباتك المحفوظة على هذا المتصفح لمدة 30 يوماً. اضغط على الطلب لمتابعته مباشرة.</p>
    <div class="mt-6 space-y-4">
        @forelse($orders as $order)
            <a href="{{ route('orders.track', ['token' => $order->tracking_token]) }}" class="block rounded-2xl border border-border bg-card p-5">
                <div class="flex justify-between gap-3"><strong>{{ $order->order_number }}</strong><span>{{ $order->total }} ₪</span></div>
                <p class="mt-2 font-semibold text-primary">{{ $order->statusMessage() }}</p>
                <p class="mt-2 text-sm text-muted-foreground">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                <span class="mt-3 inline-block font-bold">متابعة الطلب ←</span>
            </a>
        @empty
            <div class="rounded-2xl border border-border p-6">
                <p>لا توجد طلبات محفوظة على هذا المتصفح.</p>
                <p class="mt-2 text-sm">إذا طلبت من جهاز آخر، افتح رابط متابعة الطلب المحفوظ لديك. لا يمكن استرجاع تفاصيل الطلب برقم الهاتف وحده.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block font-bold text-primary">تصفح القائمة</a>
            </div>
        @endforelse
    </div>
</main>
