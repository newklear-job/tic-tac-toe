<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Debugbar;
class NewStep implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;
    protected $message;
    protected $room_id;

    public function __construct(User $user, $message, $room_id)
    {
        $this->user = $user;
        $this->message = $message;
        $this->room_id = $room_id;
        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastWith()
    {
        // This must always be an array. Since it will be parsed with json_encode()
        return [
            'user' => $this->user->name,
            'message' => $this->message,
        ];
    }

    public function broadcastAs()
    {
        return 'newMessage';
    }

    public function broadcastOn()
    {
        return new PresenceChannel('message.'.$this->room_id);
    }
}