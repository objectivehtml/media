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

class MovedModelToDisk
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;

    public $fromDisk;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $model, $fromDisk)
    {
        $this->model = $model;
        $this->fromDisk = $fromDisk;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(get_class($this->model).':'.$this->model->id);
    }
}
