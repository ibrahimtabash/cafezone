<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders', fn ($user) => $user->canManageOrders() || $user->canOnlyViewOrders());

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
