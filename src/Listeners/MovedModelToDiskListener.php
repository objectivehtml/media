<?php

namespace Objectivehtml\Media\Listeners;

use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Jobs\RemoveFileFromDisk;
use Objectivehtml\Media\Events\MovedModelToDisk;

class MovedModelToDiskListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Objectivehtml\Media\Events\MovedModelToDisk  $event
     * @return void
     */
    public function handle(MovedModelToDisk $event)
    {
        $delay = now()->addSeconds(app(MediaService::class)->config('temp.delay', 3600));
        
        RemoveFileFromDisk::dispatch(
            $event->fromDisk, $event->model->relative_path
        )->delay($delay);
    }
}