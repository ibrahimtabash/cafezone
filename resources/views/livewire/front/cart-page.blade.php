<main class="flex-1">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="font-serif text-3xl text-primary">السلة وإتمام الطلب</h1>
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

            <form
                class="rounded-3xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] space-y-4 h-fit lg:sticky lg:top-24">
                <h2 class="font-serif text-xl text-primary">بيانات التوصيل</h2>
                <label class="block">
                    <span class="text-sm font-medium text-foreground">الاسم</span>
                    <div class="mt-1">
                        {{-- <input class="field" placeholder="اسمك الكامل" value=""> --}}
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
                        {{-- <input class="field" placeholder="05X XXX XXXX" dir="ltr" value=""> --}}
                        <input wire:model="customer_phone" placeholder="05X XXX XXXX" dir="ltr" class="field" />
                        @error('customer_phone')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-foreground">العنوان</span>
                    <div class="mt-1">
                        {{-- <input class="field" placeholder="الحي، الشارع، رقم البناية" value=""> --}}
                        <input wire:model="address" placeholder="الحي، الشارع، رقم البناية" class="field" />
                        @error('address')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-foreground">اكتب ملاحظتك هنا!</span>
                    <div class="mt-1">
                        {{-- <input class="field" placeholder="مثال: رام الله — البالوع" value=""> --}}
                        <textarea wire:model="notes" placeholder="مثال: زيادة طحينية — بدون شطة .." class="field"></textarea>
                    </div>
                </label>
                {{-- <label class="block">
                    <span class="text-sm font-medium text-foreground">سعر التوصيل
                        (₪)</span>
                    <div class="mt-1">
                        <input min="0" class="field" placeholder="0" dir="ltr" type="number"
                            value="">
                    </div>
                </label> --}}
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
                <div class="border-t border-border pt-4 space-y-1 text-sm">

                    {{-- <div class="flex justify-between">
                        <span class="text-muted-foreground">المجموع</span>
                        <span>{{ number_format($this->subtotal, 2) }} ₪</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-muted-foreground">التوصيل</span>
                        <span>{{ number_format($delivery_fee, 2) }} ₪</span>
                    </div>

                    <div class="flex justify-between font-serif text-xl text-primary pt-2">
                        <span>الإجمالي</span>
                        <span>{{ number_format($this->total, 2) }} ₪</span>
                    </div> --}}
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">المجموع</span>
                        <span>{{ number_format($this->subtotal, 2) }} ₪</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-muted-foreground">التوصيل</span>
                        <span>{{ number_format($this->delivery_fee, 2) }} ₪</span>
                    </div>

                    <div class="flex justify-between font-serif text-xl text-primary pt-2">
                        <span>الإجمالي</span>
                        <span>{{ number_format($this->total, 2) }} ₪</span>
                    </div>
                </div>
                <button wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder" type="button"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-full btn-hero px-6 py-3 font-semibold">
                    <span wire:loading.remove wire:target="placeOrder">إرسال الطلب</span>
                    <span wire:loading wire:target="placeOrder">جاري الإرسال...</span>
                </button>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-full btn-hero px-6 py-3 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-send h-4 w-4" aria-hidden="true">
                        <path
                            d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z">
                        </path>
                        <path d="m21.854 2.147-10.94 10.939"></path>
                    </svg> إرسال الطلب عبر واتساب</button>
                <p class="text-[11px] text-center text-muted-foreground">سيتم فتح واتساب لإكمال إرسال الطلب.
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
    @if ($orderSuccess)
        <div
            class="fixed top-6 right-6 z-50 w-[320px] rounded-2xl border border-green-500/20 bg-white/90 backdrop-blur-xl shadow-lg p-4 animate-fade-in">

            <div class="flex items-start gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>

                <div class="flex-1">
                    <div class="text-sm font-semibold text-green-700">
                        تم إرسال الطلب بنجاح 🎉
                    </div>
                    <div class="text-xs text-muted-foreground mt-1">
                        سيتم التواصل معك قريباً لتأكيد الطلب
                    </div>
                </div>

                <button wire:click="$set('orderSuccess', false)" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>

            </div>
        </div>
    @endif
</main>
