<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendSystemNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $message;
    private $relatedUrl;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        $message,
        $relatedUrl,
    ) {
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
        return new PresenceChannel('system.notification');
    }

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
