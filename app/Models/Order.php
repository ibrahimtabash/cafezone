<?php

namespace App\Models;

use App\Events\OrderChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    protected $fillable = [
        'payment_method_id', 'payment_details', 'payment_receipt', 'payment_status', 'payment_note',
        'payment_confirmed_at', 'payment_confirmed_by', 'tracking_token', 'checkout_key', 'opened_at',
        'order_number',
        'order_type',
        'dining_table_id',
        'customer_name',
        'customer_phone',
        'delivery_area_id',
        'address',
        'subtotal',
        'delivery_fee',
        'total',
        'notes',
        'status',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'payment_confirmed_at' => 'datetime',
        'opened_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function deliveryArea(): BelongsTo
    {
        return $this->belongsTo(DeliveryArea::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(OrderUpdate::class);
    }

    public static function statusLabels(): array
    {
        return ['pending' => 'بانتظار تأكيد الدفع', 'confirmed' => 'تم تأكيد الدفع — في الانتظار',
            'preparing' => 'قيد التجهيز في المطبخ', 'on_the_way' => 'جاهز — توجه لاستلام طلبك',
            'completed' => 'تم استلام الطلب', 'cancelled' => 'الطلب ملغي'];
    }

    public function statusMessage(): string
    {
        if ($this->payment_status === 'rejected') {
            return 'لم يتم قبول إثبات الدفع: '.$this->payment_note;
        }
        if ($this->status === 'on_the_way' && $this->order_type === 'delivery') {
            return 'طلبك في الطريق إليك';
        }

        return self::statusLabels()[$this->status] ?? $this->status;
    }

    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            if ($order->isDirty('status')) {
                if (! array_key_exists($order->status, self::statusLabels())) {
                    throw ValidationException::withMessages(['status' => 'حالة الطلب غير صالحة.']);
                }
                if ($order->tracking_token && ! in_array($order->status, ['pending', 'cancelled']) && $order->payment_status !== 'confirmed') {
                    throw ValidationException::withMessages(['status' => 'يجب تأكيد الدفع أولاً.']);
                }
            }
        });
        static::saved(function (Order $order) {
            if ($order->wasRecentlyCreated || $order->wasChanged(['status', 'payment_status'])) {
                $order->updates()->create(['status' => $order->status, 'payment_status' => $order->payment_status ?? 'unpaid', 'message' => $order->statusMessage()]);
                OrderChanged::dispatch($order->id, $order->tracking_token, $order->wasRecentlyCreated, $order->statusMessage());
            } elseif ($order->wasChanged('opened_at')) {
                OrderChanged::dispatch($order->id, $order->tracking_token);
            }
        });
    }
}
