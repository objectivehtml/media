<?php

namespace Objectivehtml\MediaManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\MediaManager\MediaService;
use Objectivehtml\MediaManager\Exceptions\CannotMoveModelException;

class MoveModelToDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $disk;

    protected $model;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model, $disk)
    {
        $this->disk = $disk;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        try {
            app(MediaService::class)->changeDisk($this->model, $this->disk);
        }
        catch(CannotMoveModelException $e) {
            //
        }
    }
}
