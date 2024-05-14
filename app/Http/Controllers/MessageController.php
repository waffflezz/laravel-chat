<?php

namespace App\Http\Controllers;

use App\Events\StoreMessageEvent;
use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Services\PusherNotificationService;

class MessageController extends Controller
{
    private PusherNotificationService $pusherNotificationService;

    public function __construct(PusherNotificationService $pusherNotificationService)
    {
        $this->pusherNotificationService = $pusherNotificationService;
    }

    public function index(User $otherUser = null)
    {
        $currentUser = auth()->user();
        if (!$otherUser) {
            $otherUser = $currentUser;
        }

        $users = User::all(['id', 'name']);

        $messages = Message::where(function ($query) use ($currentUser, $otherUser) {
            $query->where('from_id', $currentUser->id)->where('to_id', $otherUser->id);
        })->orWhere(function ($query) use ($currentUser, $otherUser) {
            $query->where('from_id', $otherUser->id)->where('to_id', $currentUser->id);
        })->get();

        $messages = MessageResource::collection($messages)->resolve();

        return inertia('Message/Main', compact(
            'currentUser',
            'otherUser',
            'users',
            'messages'
        ));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['from_id'] = $request->from_id;
        $data['to_id'] = $request->to_id;

        $message = Message::create($data);

        broadcast(new StoreMessageEvent($message))->toOthers();

        $this->pusherNotificationService->notify($data['to_id'], $data['from_id'], $request->body);

        return MessageResource::make($message)->resolve();
    }
}
