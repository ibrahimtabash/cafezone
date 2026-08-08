<?php

namespace App\Livewire\Front;

use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CartPage extends Component
{
    public array $cart = [];

    public bool $orderSuccess = false;

    public string $customer_name = '';
    public string $customer_phone = '';
    public string $address = '';
    public ?int $delivery_area_id = null;
    public string $notes = '';

    public float $delivery_fee = 0;
    public string $order_type = 'delivery';
    public ?int $dining_table_id = null;
    public ?string $dining_table_name = null;

    // public float $total = 0;

    public function mount()
    {
        $this->cart = session('cart', []);
        $context = session('order_context', ['type' => 'delivery']);
        $this->order_type = $context['type'] ?? 'delivery';
        $this->dining_table_id = $context['table_id'] ?? null;
        $this->dining_table_name = $context['table_name'] ?? null;
    }

    public function increase($itemId): void
    {
        $cart = session('cart', []);

        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity']++;
        }

        $this->syncCart($cart);
    }

    public function decrease($itemId): void
    {
        $cart = session('cart', []);

        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity']--;

            if ($cart[$itemId]['quantity'] <= 0) {
                unset($cart[$itemId]);
            }
        }

        $this->syncCart($cart);
    }

    public function remove($itemId): void
    {
        $cart = session('cart', []);

        unset($cart[$itemId]);

        $this->syncCart($cart);
    }

    /**
     * 🔥 مركز التحكم بالسلة
     */
    private function syncCart(array $cart): void
    {
        session(['cart' => $cart]);
        $this->cart = $cart;

        // 🔥 لازم نفس event اللي يستخدمه CartCounter
        $this->dispatch('cart-updated');
    }


    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(
            fn($item) => $item['price'] * $item['quantity']
        );
    }
    //
    public function getDeliveryFeeProperty()
    {
        if ($this->order_type !== 'delivery') {
            return 0;
        }
        if (!$this->delivery_area_id) {
            return 0;
        }

        $area = DeliveryArea::find($this->delivery_area_id);

        return $area?->delivery_fee ?? 0;
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->delivery_fee;
    }
    public function setDeliveryArea($value)
    {
        $this->delivery_area_id = $value;

        if (!$value) {
            $this->delivery_fee = 0;
            $this->total = $this->subtotal;
            return;
        }

        $area = DeliveryArea::find($value);

        $this->delivery_fee = $area?->delivery_fee ?? 0;

        $this->total = $this->subtotal + $this->delivery_fee;
    }

    // public function updatedDeliveryAreaId($value)
    // {
    //     if (!$value) {
    //         $this->delivery_fee = 0;
    //         return;
    //     }

    //     $area = DeliveryArea::find($value);

    //     $this->delivery_fee = $area?->delivery_fee ?? 0;
    // }

    // public function placeOrder()
    // {
    //     $this->validate([
    //         'customer_name' => 'required|min:3',
    //         'customer_phone' => 'required|min:6',
    //         'address' => 'required|min:5',
    //         'delivery_area_id' => 'required|exists:delivery_areas,id',
    //     ], [
    //         'customer_name.required' => 'الاسم مطلوب',
    //         'customer_phone.required' => 'رقم الجوال مطلوب',
    //         'address.required' => 'العنوان مطلوب',
    //         'delivery_area_id.required' => 'اختر منطقة التوصيل',
    //     ]);

    //     $deliveryArea = \App\Models\DeliveryArea::findOrFail($this->delivery_area_id);

    //     $subtotal = $this->subtotal;
    //     $deliveryFee = $this->delivery_fee;
    //     $total = $subtotal + $deliveryFee;

    //     $order = \App\Models\Order::create([
    //         'order_number' => 'ORD-' . strtoupper(uniqid()),
    //         'customer_name' => $this->customer_name,
    //         'customer_phone' => $this->customer_phone,
    //         'delivery_area_id' => $this->delivery_area_id,
    //         'address' => $this->address,
    //         'subtotal' => $subtotal,
    //         'delivery_fee' => $deliveryFee,
    //         'total' => $total,
    //         'notes' => $this->notes,
    //     ]);

    //     foreach ($this->cart as $item) {
    //         \App\Models\OrderItem::create([
    //             'order_id' => $order->id,
    //             'menu_item_id' => $item['id'],
    //             'name' => $item['name'],
    //             'price' => $item['price'],
    //             'quantity' => $item['quantity'],
    //             'total' => $item['price'] * $item['quantity'],
    //         ]);
    //     }

    //     session()->forget('cart');
    //     $this->cart = [];

    //     $this->reset([
    //         'customer_name',
    //         'customer_phone',
    //         'address',
    //         'delivery_area_id',
    //         'notes'
    //     ]);

    //     $this->orderSuccess = true;

    //     $this->dispatch('cartUpdated');
    // }
    /* ---------------- ORDER ---------------- */

    public function placeOrder()
    {
        if ($this->cart === []) {
            $this->addError('cart', 'السلة فارغة. أضف صنفًا واحدًا على الأقل.');
            return;
        }

        $rules = ['notes' => 'nullable|string|max:1000'];
        if ($this->order_type === 'delivery') {
            $rules += [
                'customer_name' => 'required|string|min:2|max:100',
                'customer_phone' => 'required|string|min:6|max:30',
                'address' => 'required|string|min:5|max:500',
                'delivery_area_id' => 'required|exists:delivery_areas,id',
            ];
        } elseif ($this->order_type === 'takeaway') {
            $rules += [
                'customer_name' => 'required|string|min:2|max:100',
                'customer_phone' => 'required|string|min:6|max:30',
            ];
        } else {
            $rules['dining_table_id'] = 'required|exists:dining_tables,id';
        }
        $this->validate($rules);

        $order = DB::transaction(function () {
            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('ymd-His') . '-' . strtoupper(Str::random(4)),
                'order_type' => $this->order_type,
                'dining_table_id' => $this->order_type === 'dine_in' ? $this->dining_table_id : null,
                'customer_name' => $this->customer_name ?: null,
                'customer_phone' => $this->customer_phone ?: null,
                'delivery_area_id' => $this->order_type === 'delivery' ? $this->delivery_area_id : null,
                'address' => $this->order_type === 'delivery' ? $this->address : null,
                'subtotal' => $this->subtotal,
                'delivery_fee' => $this->delivery_fee,
                'total' => $this->total,
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id, 'menu_item_id' => $item['id'],
                    'name' => $item['name'], 'price' => $item['price'],
                    'quantity' => $item['quantity'], 'total' => $item['price'] * $item['quantity'],
                ]);
            }
            return $order;
        });

        // بناء رسالة واتساب
        $typeLabel = match ($this->order_type) {
            'dine_in' => "داخل الكافي - {$this->dining_table_name}",
            'takeaway' => 'طلب خارجي / استلام من الكافي',
            default => 'توصيل',
        };
        $message = "🛒 طلب جديد رقم: {$order->order_number}\n";
        $message .= "🏷️ النوع: {$typeLabel}\n\n";
        if ($this->customer_name) $message .= "👤 الاسم: {$this->customer_name}\n";
        if ($this->customer_phone) $message .= "📱 الهاتف: {$this->customer_phone}\n";
        if ($this->order_type === 'delivery') $message .= "📍 العنوان: {$this->address}\n";
        if ($this->notes) $message .= "📝 ملاحظات: {$this->notes}\n";

        $message .= "📦 الطلب:\n";

        foreach ($this->cart as $item) {
            $message .= "- {$item['name']} x {$item['quantity']} = " . ($item['price'] * $item['quantity']) . " ₪\n";
        }

        $message .= "\n💰 المجموع: {$order->subtotal} ₪";
        if ($this->order_type === 'delivery') $message .= "\n🚚 التوصيل: {$order->delivery_fee} ₪";
        $message .= "\n💵 الإجمالي: {$order->total} ₪";

        $phone = preg_replace('/\D+/', '', config('services.cafe.whatsapp_number'));

        $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        session()->forget('cart');
        $this->cart = [];

        $this->reset([
            'customer_name',
            'customer_phone',
            'address',
            'delivery_area_id',
            'notes',
            'delivery_fee'
        ]);


        $this->orderSuccess = true;

        $this->dispatch('cart-updated');
        $this->dispatch('open-whatsapp', url: $whatsappUrl);
    }

    public function render()
    {
        return view('livewire.front.cart-page', [
            'areas' => DeliveryArea::where('is_active', true)->get(),
        ])->layout('layouts.app');
    }
}
