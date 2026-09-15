<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cookie;

class CustomerOrders
{
    public const COOKIE = 'cafe_customer_orders';

    public static function tokens(): array
    {
        $tokens = json_decode(request()->cookie(self::COOKIE, '[]'), true);

        return is_array($tokens) ? array_slice(array_values(array_filter($tokens,
            fn ($token) => is_string($token) && preg_match('/^[A-Za-z0-9]{64}$/', $token)
        )), 0, 12) : [];
    }

    public static function remember(Order $order): void
    {
        if (! $order->tracking_token) {
            return;
        }

        $tokens = array_slice(array_values(array_unique([$order->tracking_token, ...self::tokens()])), 0, 12);
        // Laravel encrypts this HttpOnly cookie; it survives session expiry and browser restarts.
        Cookie::queue(Cookie::make(self::COOKIE, json_encode($tokens), 60 * 24 * 30,
            '/', null, request()->isSecure(), true, false, 'lax'));
    }

    public static function activeOrder(): ?Order
    {
        $tokens = self::tokens();

        return $tokens ? Order::whereIn('tracking_token', $tokens)
            ->whereNotIn('status', ['completed', 'cancelled'])->latest('id')->first() : null;
    }
}
