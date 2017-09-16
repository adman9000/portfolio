<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PusherEvent implements ShouldBroadcast
{
    use SerializesModels;

     public $message;

    protected $broadcast_as;
     
    /**
     * Create a new event instance. 
     *
     * @return void
     */
    public function __construct($message, $broadcast_as='portfolio\\prices')
    {
        //  
        $this->message = $message;
        $this->broadcast_as = $broadcast_as;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('kraken');
    }

      public function broadCastAs() {

        return $this->broadcast_as;
    }
    
}
