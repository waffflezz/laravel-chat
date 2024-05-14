<?php

namespace App\Services;

use App\Models\User;
use Pusher\PushNotifications\PushNotifications;

class PusherNotificationService
{
    protected $beamsClient;

    public function __construct()
    {
        $this->beamsClient = new PushNotifications([
            'instanceId' => env('PUSHER_BEAMS_INSTANCE_ID'),
            'secretKey' => env('PUSHER_BEAMS_SECRET_KEY'),
        ]);
    }

    public function notify($toId, $fromId, $message)
    {
        $nameFrom = User::query()->find($fromId)->name;
        $this->beamsClient->publishToInterests(
            ['user-' . $toId . '-' . $fromId],
            [
                'web' => [
                    'notification' => [
                        'title' => 'Сообщение от ' . $nameFrom,
                        'body' => $message,
                        'deep_link' => route('index', ['otherUser' => $fromId])
                    ]
                ]
            ]
        );
    }
}
