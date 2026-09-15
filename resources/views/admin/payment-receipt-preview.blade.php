<div class="space-y-3 text-center">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
        <img src="{{ route('orders.receipt', $order) }}" alt="إثبات الدفع للطلب {{ $order->order_number }}"
            class="mx-auto max-h-[70vh] w-auto max-w-full rounded-xl object-contain">
    </div>
    <p class="text-sm text-gray-500">إثبات الدفع للطلب {{ $order->order_number }}</p>
</div>
