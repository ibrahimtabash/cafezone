<main class="flex-1">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="font-serif text-3xl text-primary">السلة وإتمام الطلب</h1>
        <div class="mt-4 inline-flex rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
            @if($order_type === 'dine_in') الطلب داخل الكافي — {{ $dining_table_name }}
            @elseif($order_type === 'takeaway') طلب خارجي — استلام من الكافي
            @else طلب توصيل @endif
        </div>
        <div class="mt-2 h-1 w-24 tatreez-border"></div>
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]">
            <div class="space-y-3 overflow-x-hidden">
                @forelse($cart as $item)
                    <div
                        class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-border bg-card p-4">

                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                            class="h-16 w-16 shrink-0 rounded-xl object-cover">

                        <div class="flex-1">
                            <div class="font-serif text-lg">
                                {{ $item['name'] }}
                            </div>

                            <div class="text-sm text-muted-foreground">
                                {{ number_format($item['price'], 2) }} ₪ / للقطعة
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-1 rounded-full bg-secondary p-1">

                            <button wire:click="decrease({{ $item['id'] }})"
                                class="rounded-full bg-background p-1.5 hover:bg-primary hover:text-primary-foreground transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-minus h-3.5 w-3.5" aria-hidden="true">
                                    <path d="M5 12h14"></path>
                                </svg>
                            </button>

                            <span class="w-8 text-center font-semibold">
                                {{ $item['quantity'] }}
                            </span>

                            <button wire:click="increase({{ $item['id'] }})"
                                class="rounded-full bg-background p-1.5 hover:bg-primary hover:text-primary-foreground transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-plus h-3.5 w-3.5" aria-hidden="true">
                                    <path d="M5 12h14"></path>
                                    <path d="M12 5v14"></path>
                                </svg>
                            </button>

                        </div>

                        <div class="w-full sm:w-20 text-left font-serif text-lg text-primary">
                            {{ number_format($item['price'] * $item['quantity'], 2) }} ₪
                        </div>

                        <button wire:click="remove({{ $item['id'] }})"
                            class="text-muted-foreground hover:text-red-500" title="حذف من السلة">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-trash-2">
                                <path d="M3 6h18"></path>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>

                    </div>

                @empty
                    <div class="mx-auto max-w-md p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-shopping-bag mx-auto h-16 w-16 text-muted-foreground"
                            aria-hidden="true">
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                            <path d="M3.103 6.034h17.794"></path>
                            <path
                                d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z">
                            </path>
                        </svg>
                        <h1 class="mt-4 font-serif text-2xl text-primary">سلتك فارغة</h1>
                        <p class="mt-2 text-sm text-muted-foreground">أضف بعض الأطباق الشهية من القائمة.</p><a
                            href="/"
                            class="mt-6 inline-block rounded-full bg-primary px-6 py-3 text-primary-foreground btn-hero font-semibold">تصفّح
                            القائمة</a>
                    </div>
                @endforelse
            </div>

            <form wire:submit="placeOrder"
                class="rounded-3xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] space-y-4 h-fit lg:sticky lg:top-24">
                <h2 class="text-xl font-bold">بيانات الدفع</h2>
                <label class="block">طريقة الدفع
                    <select class="field" wire:model.live="payment_method_id">
                        <option value="">اختر البنك أو المحفظة</option>
                        @foreach($paymentMethods as $method)<option value="{{ $method->id }}">{{ $method->name }}</option>@endforeach
                    </select>
                </label>
                @error('payment_method_id')<p class="text-red-600">{{ $message }}</p>@enderror
                @if($paymentMethods->isEmpty())<p role="alert">لا توجد طرق دفع متاحة حالياً. يرجى مراجعة الكاشير.</p>@endif
                @if($selectedMethod = $paymentMethods->firstWhere('id', $payment_method_id))
                    <div class="rounded-xl border border-border p-4 space-y-2">
                        <p>صاحب الحساب: {{ $selectedMethod->account_name }}</p>
                        <p>رقم الحساب / المحفظة: <bdi>{{ $selectedMethod->account_number }}</bdi></p>
                        <p>{{ $selectedMethod->instructions }}</p>
                        @if($selectedMethod->qr_code)<img class="mx-auto h-44 w-44 object-contain" src="{{ Storage::disk('public')->url($selectedMethod->qr_code) }}" alt="رمز QR للدفع">@endif
                        <p>حوّل المبلغ الإجمالي ثم أرفق صورة إثبات الدفع أدناه.</p>
                    </div>
                @endif
                <h2 class="font-serif text-xl text-primary">
                    {{ $order_type === 'delivery' ? 'بيانات التوصيل' : ($order_type === 'takeaway' ? 'بيانات الاستلام' : 'تفاصيل طلب الطاولة') }}
                </h2>
                @error('cart') <p class="rounded-xl bg-red-50 p-3 text-sm text-red-600">{{ $message }}</p> @enderror
                <label class="block">
                    <span class="text-sm font-medium text-foreground">الاسم (اختياري)</span>
                    <div class="mt-1">
                        <input wire:model="customer_name" placeholder="اسمك الكامل" class="field" />
                        @error('customer_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-foreground">رقم
                        الجوال</span>
                    <div class="mt-1">
                        <input wire:model="customer_phone" placeholder="05X XXX XXXX" dir="ltr" class="field" />
                        @error('customer_phone')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                @if($order_type === 'delivery')
                <label class="block">
                    <span class="text-sm font-medium text-foreground">العنوان</span>
                    <div class="mt-1">
                        <input wire:model="address" placeholder="الحي، الشارع، رقم البناية" class="field" />
                        @error('address')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                @endif
                @if($order_type === 'delivery')
                <label class="block">
                    <span class="text-sm font-medium text-foreground">ملاحظات الطلب (اختياري)</span>
                    <div class="mt-1">
                        <textarea wire:model="notes" placeholder="مثال: بدون سكر، بدون شطة، أو أي طلب خاص…" class="field" rows="3"></textarea>
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-foreground">منطقة التوصيل</span>

                    <div class="mt-1">
                        <select wire:model="delivery_area_id" wire:change="setDeliveryArea($event.target.value)"
                            class="field">
                            <option value="">اختر المنطقة</option>

                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">
                                    {{ $area->name }} ({{ $area->delivery_fee }} ₪)
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_area_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                @endif
                <label class="block">إرفاق إشعار الدفع (JPG / PNG / WebP حتى 5 MB)
                    <input class="field" type="file" accept="image/jpeg,image/png,image/webp" wire:model="payment_receipt">
                </label>
                <label class="block">أو تصوير إشعار الدفع
                    <input class="field" type="file" accept="image/*" capture="environment" wire:model="payment_receipt">
                </label>
                <p wire:loading wire:target="payment_receipt">جاري رفع إثبات الدفع…</p>
                @error('payment_receipt')<p class="text-red-600">{{ $message }}</p>@enderror
                @if($payment_receipt && !$errors->has('payment_receipt'))<p class="text-green-700">تم إرفاق إثبات الدفع.</p>@endif
                <div class="border-t border-border pt-4 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">المجموع</span>
                        <span>{{ number_format($this->subtotal, 2) }} ₪</span>
                    </div>

                    @if($order_type === 'delivery')
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">التوصيل</span>
                        <span>{{ number_format($this->delivery_fee, 2) }} ₪</span>
                    </div>
                    @endif

                    <div class="flex justify-between font-serif text-xl text-primary pt-2">
                        <span>الإجمالي</span>
                        <span>{{ number_format($this->total, 2) }} ₪</span>
                    </div>
                </div>
                <button wire:loading.attr="disabled" wire:target="placeOrder,payment_receipt" type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-full btn-hero px-6 py-3 font-semibold">
                    <span wire:loading.remove wire:target="placeOrder">إرسال الطلب وإثبات الدفع</span>
                    <span wire:loading wire:target="placeOrder">جاري الإرسال...</span>
                </button>
                <p class="text-[11px] text-center text-muted-foreground">سيصل طلبك للكاشير لمراجعة الدفع وتتابع المراحل على المنصة.
                </p>
            </form>
        </div>
        <style>
            .field {
                width: 100%;
                padding: 0.65rem 0.9rem;
                border-radius: 0.75rem;
                border: 1px solid var(--border);
                background: var(--background);
                font-size: 0.9rem;
                outline: none;
                transition: border-color .2s, box-shadow .2s;
            }

            .field:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px oklch(0.46 0.14 18 / 0.15);
            }
        </style>
    </div>
</main>
