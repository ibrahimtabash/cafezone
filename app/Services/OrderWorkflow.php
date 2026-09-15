<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderWorkflow
{
    public static function advance(Order $order, string $status): void
    {
        abort_unless(auth()->user()?->canManageOrders(), 403);

        DB::transaction(function () use ($order, $status) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            $locked->update([
                'status' => $status,
                'opened_at' => $locked->opened_at ?? now(),
            ]);
        });
    }

    public static function rollBack(Order $order): void
    {
        abort_unless(auth()->user()?->canManageOrders(), 403);

        DB::transaction(function () use ($order) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            $previous = [
                'confirmed' => 'pending',
                'preparing' => 'confirmed',
                'on_the_way' => 'preparing',
                'completed' => 'on_the_way',
            ][$locked->status] ?? null;

            if (! $previous) {
                throw ValidationException::withMessages(['status' => 'لا توجد مرحلة سابقة يمكن الرجوع إليها.']);
            }

            $changes = ['status' => $previous, 'opened_at' => $locked->opened_at ?? now()];
            if ($previous === 'pending') {
                $changes += [
                    'payment_status' => 'pending',
                    'payment_confirmed_at' => null,
                    'payment_confirmed_by' => null,
                ];
            }
            $locked->update($changes);
        });
    }

    public static function setStatus(Order $order, string $status): void
    {
        abort_unless(auth()->user()?->canManageOrders(), 403);

        if (! array_key_exists($status, Order::statusLabels())) {
            throw ValidationException::withMessages(['status' => 'حالة الطلب غير صالحة.']);
        }

        DB::transaction(function () use ($order, $status) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            $changes = ['status' => $status, 'opened_at' => $locked->opened_at ?? now()];

            if ($status === 'pending') {
                $changes += ['payment_status' => 'pending', 'payment_confirmed_at' => null, 'payment_confirmed_by' => null];
            } elseif (! in_array($status, ['cancelled']) && $locked->payment_status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'يجب تأكيد الدفع قبل اختيار هذه الحالة.']);
            }

            $locked->update($changes);
        });
    }

    public static function payment(Order $order, bool $confirmed, ?string $note = null): void
    {
        abort_unless(auth()->user()?->canManageOrders(), 403);
        DB::transaction(function () use ($order, $confirmed, $note) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== 'pending' || ! in_array($locked->payment_status, ['pending', 'rejected', 'unpaid'])) {
                throw ValidationException::withMessages(['payment' => 'تمت معالجة الدفع مسبقاً أو ألغي الطلب.']);
            }
            if (! $confirmed && ! filled($note)) {
                throw ValidationException::withMessages(['note' => 'سبب رفض الدفع مطلوب.']);
            }
            $locked->update(['payment_status' => $confirmed ? 'confirmed' : 'rejected',
                'status' => $confirmed ? 'confirmed' : 'pending', 'payment_note' => $note,
                'opened_at' => $locked->opened_at ?? now(),
                'payment_confirmed_at' => $confirmed ? now() : null,
                'payment_confirmed_by' => $confirmed ? auth()->id() : null]);
        });
    }
}
