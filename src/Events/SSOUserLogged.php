<?php

namespace Nhd\Foundation\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SSOUserLogged
{
    use Dispatchable;

    public $user;
    public $data;
    public $isNahadUser;

    public function __construct($user, $data = [], $isNahadUser = false)
    {
        $this->user = $user;
        $this->data = $data;
        $this->isNahadUser = $isNahadUser;
    }
}
