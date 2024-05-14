<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('store_messages.{fromId}.{toId}', function ($user, $fromId, $toId) {
    return $user->id === (int) $fromId || $user->id === (int) $toId;
});
