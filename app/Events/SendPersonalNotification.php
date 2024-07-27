<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendPersonalNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $userId;
    private $message;
    private $relatedUrl;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        $userId,
        $message,
        $relatedUrl,
    ) {
        $this->userId = $userId;
        $this->message = $message;
        $this->relatedUrl = $relatedUrl;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("notification.{$this->userId}");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification.new';
    }


    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'related_url' => $this->relatedUrl
        ];
    }
}
