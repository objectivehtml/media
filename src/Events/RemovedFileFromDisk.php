<?php

namespace Objectivehtml\Media\Events;

use Objectivehtml\Media\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemovedFileFromDisk
{
    use Dispatchable, SerializesModels;

    public $disk;

    public $relativePath;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($disk, $relativePath)
    {
        $this->disk = $disk;
        $this->relativePath = $relativePath;
    }

}
