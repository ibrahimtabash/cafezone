@php
    $steps = [
        ['status' => 'pending', 'title' => 'مراجعة الدفع', 'image' => 'waiting.avif'],
        ['status' => 'confirmed', 'title' => 'تم تأكيد الدفع', 'image' => 'payment-confirmed.avif'],
        ['status' => 'preparing', 'title' => 'قيد التجهيز', 'image' => 'preparing.avif'],
        ['status' => 'on_the_way', 'title' => $order->order_type === 'delivery' ? 'في الطريق' : 'جاهز للاستلام', 'image' => 'ready.avif'],
    ];
    $statusIndex = collect($steps)->search(fn ($step) => $step['status'] === $order->status);
    $statusIndex = $order->status === 'completed' ? count($steps) : ($statusIndex === false ? 0 : $statusIndex);
    $currentImage = match ($order->status) {
        'confirmed' => 'payment-confirmed.avif', 'preparing' => 'preparing.avif',
        'on_the_way', 'completed' => 'ready.avif', default => 'waiting.avif',
    };
    $paymentLabel = ['pending' => 'بانتظار مراجعة الكاشير', 'confirmed' => 'تم تأكيد الدفع', 'rejected' => 'يحتاج إعادة مراجعة', 'unpaid' => 'غير مدفوع'][$order->payment_status] ?? $order->payment_status;
@endphp

<main class="relative overflow-hidden bg-[radial-gradient(circle_at_top_right,rgba(120,53,15,.10),transparent_35%)]" wire:poll.30s data-order-token="{{ $token }}">
    <div class="mx-auto w-full max-w-5xl px-4 py-5 sm:py-7">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <a href="{{ route('orders.mine') }}" class="inline-flex items-center gap-2 text-xs font-bold text-primary hover:opacity-70">→ العودة إلى طلباتي</a>
                <h1 class="mt-1 font-serif text-2xl font-bold text-primary sm:text-3xl">متابعة طلبك</h1>
            </div>
            <div class="rounded-xl border border-primary/15 bg-card px-3 py-2 text-left shadow-sm">
                <span class="block text-[9px] text-muted-foreground">رقم الطلب</span>
                <strong class="block font-mono text-xs text-primary sm:text-sm" dir="ltr">{{ $order->order_number }}</strong>
            </div>
        </div>

        <section>
            <div class="grid items-center gap-3 overflow-hidden rounded-[1.7rem] bg-[linear-gradient(135deg,#78323b,#461c22)] p-4 shadow-[0_18px_45px_rgba(66,25,31,.25)] sm:p-5 md:grid-cols-[minmax(0,1fr)_280px] md:gap-6">
                <div class="order-2 text-primary-foreground md:order-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-bold">
                            <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold opacity-50"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-gold"></span></span> تحديث مباشر
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-primary-foreground/60">حالة طلبك الآن</p>
                    <h2 role="status" aria-live="polite" class="mt-1 font-serif text-xl font-bold leading-relaxed sm:text-2xl">{{ $order->statusMessage() }}</h2>
                    @if($order->status === 'on_the_way' && $order->order_type !== 'delivery')
                        <p class="mt-4 rounded-2xl border border-gold/30 bg-gold/15 p-4 font-bold">طلبك جاهز 🎉 توجه إلى نقطة الاستلام وأبرز رقم الطلب.</p>
                    @elseif($order->status === 'completed')
                        <p class="mt-4 text-sm text-primary-foreground/70">صحة وهنا! سعدنا بخدمتك.</p>
                    @endif
                    <div class="mt-4" data-alert-activation-wrap>
                        <button type="button" data-enable-order-alerts aria-pressed="false"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-lg transition hover:-translate-y-0.5 sm:w-auto">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                            <span data-alert-label>تفعيل إشعارات الطلب</span>
                        </button>
                    </div>
                </div>

                <div class="order-1 flex h-[210px] items-center justify-center sm:h-[235px] md:order-2 md:h-[260px]">
                    <img src="{{ asset('assets/images/order-status/'.$currentImage) }}" alt="{{ $order->statusMessage() }}"
                        class="h-full w-full max-w-[300px] object-contain drop-shadow-[0_16px_15px_rgba(0,0,0,.2)]">
                </div>
            </div>

            @if($order->status === 'cancelled')
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-center font-bold text-red-700">تم إلغاء الطلب. راجع الكاشير إذا احتجت مساعدة.</div>
            @else
                <div class="mt-4 rounded-2xl border border-primary/10 bg-card/80 px-2 py-3 shadow-[var(--shadow-soft)] backdrop-blur-sm sm:px-5 sm:py-4">
                    <div class="relative grid grid-cols-4 gap-1">
                        <div class="absolute right-[12.5%] left-[12.5%] top-6 h-0.5 rounded-full bg-border sm:top-7"></div>
                        <div class="absolute right-[12.5%] top-6 h-0.5 rounded-full bg-gradient-to-l from-primary to-gold transition-all duration-700 sm:top-7" style="width: {{ min(75, ($statusIndex / 3) * 75) }}%"></div>
                        @foreach($steps as $index => $step)
                            @php($done = $index <= $statusIndex)
                            <div class="relative z-10 text-center">
                                <div class="relative mx-auto h-12 w-12 sm:h-14 sm:w-14">
                                    <img src="{{ asset('assets/images/order-status/'.$step['image']) }}" alt="" class="h-full w-full object-contain transition {{ $done ? 'opacity-100 drop-shadow-md' : 'grayscale opacity-35' }}">
                                    @if($done)<span class="absolute -left-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[9px] font-bold text-white shadow">✓</span>@endif
                                </div>
                                <p class="mt-1.5 text-[9px] font-bold leading-tight {{ $done ? 'text-primary' : 'text-muted-foreground' }} sm:text-xs">{{ $step['title'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <div class="mt-4 grid gap-4 lg:grid-cols-[1.2fr_.8fr]">
            <section class="rounded-3xl border border-border bg-card p-5 shadow-[var(--shadow-soft)] sm:p-6">
                <div class="flex items-center justify-between gap-3"><h2 class="font-serif text-xl font-bold text-primary">تفاصيل الطلب</h2><span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold">{{ $order->items->sum('quantity') }} قطعة</span></div>
                <div class="mt-5 divide-y divide-border/70">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between gap-4 py-3"><div><strong>{{ $item->name }}</strong><span class="mr-2 text-sm text-muted-foreground">× {{ $item->quantity }}</span></div><span class="font-bold text-primary">{{ number_format((float) $item->total, 2) }} ₪</span></div>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2 rounded-2xl bg-secondary/50 p-4 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">مجموع الأصناف</span><span>{{ number_format((float) $order->subtotal, 2) }} ₪</span></div>
                    @if((float) $order->delivery_fee > 0)<div class="flex justify-between"><span class="text-muted-foreground">التوصيل</span><span>{{ number_format((float) $order->delivery_fee, 2) }} ₪</span></div>@endif
                    <div class="flex justify-between border-t border-border pt-3 text-lg font-bold text-primary"><span>الإجمالي</span><span>{{ number_format((float) $order->total, 2) }} ₪</span></div>
                </div>
                @if($order->notes)<p class="mt-4 rounded-2xl border border-border p-4 text-sm"><span class="block font-bold text-primary">ملاحظاتك</span>{{ $order->notes }}</p>@endif
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-border bg-card p-5 shadow-[var(--shadow-soft)] sm:p-6">
                    <h2 class="font-serif text-xl font-bold text-primary">بيانات الدفع</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-muted-foreground">الحالة</dt><dd class="font-bold {{ $order->payment_status === 'rejected' ? 'text-red-600' : 'text-primary' }}">{{ $paymentLabel }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted-foreground">الطريقة</dt><dd class="font-semibold">{{ $order->payment_details['name'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted-foreground">صاحب الحساب</dt><dd>{{ $order->payment_details['account_name'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted-foreground">رقم الحساب</dt><dd class="font-mono" dir="ltr">{{ $order->payment_details['account_number'] ?? '—' }}</dd></div>
                        @if($order->payment_confirmed_at)<div class="flex justify-between gap-3"><dt class="text-muted-foreground">وقت التأكيد</dt><dd>{{ $order->payment_confirmed_at->format('H:i d/m/Y') }}</dd></div>@endif
                    </dl>
                </section>
                <details class="group rounded-3xl border border-border bg-card p-5 shadow-[var(--shadow-soft)]">
                    <summary class="flex cursor-pointer list-none items-center justify-between font-bold text-primary">سجل تحديثات الطلب <span class="text-xl transition group-open:rotate-45">+</span></summary>
                    <ol class="mt-5 space-y-4 border-r-2 border-primary/15 pr-5">
                        @foreach($order->updates->sortByDesc('created_at') as $update)
                            <li class="relative"><span class="absolute -right-[1.62rem] top-1 h-3 w-3 rounded-full border-2 border-card bg-primary"></span><strong class="text-sm">{{ $update->message }}</strong><time class="mt-1 block text-xs text-muted-foreground">{{ $update->created_at->format('H:i · d/m/Y') }}</time></li>
                        @endforeach
                    </ol>
                </details>
            </div>
        </div>
        <div class="mt-6 flex flex-col justify-between gap-2 rounded-2xl border border-dashed border-primary/20 bg-primary/5 p-4 text-xs text-muted-foreground sm:flex-row">
            <span>يمكنك الخروج والعودة من «طلباتي» على هذا المتصفح خلال 30 يوماً.</span><span>احفظ الرابط إذا أردت فتح الطلب من جهاز آخر، ولا تشاركه.</span>
        </div>
    </div>
</main>
