<?php

namespace Nhd\Foundation\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NahadGroupCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vice, $management, $office;

    public function __construct($vice, $management = null, $office = null)
    {
        $this->vice = $vice;
        $this->management = $management;
        $this->office = $office;
    }
}
