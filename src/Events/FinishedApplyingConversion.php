<?php

namespace Objectivehtml\Media\Events;

use Objectivehtml\Media\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Objectivehtml\Media\Contracts\Conversion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FinishedApplyingConversion
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;

    public $conversion;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $model, Conversion $conversion)
    {
        $this->model = $model;
        $this->conversion = $conversion;
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
