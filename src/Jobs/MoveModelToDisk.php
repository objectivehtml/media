<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\MovedModelToDisk;
use Objectivehtml\Media\Exceptions\CannotMoveModelException;

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
        // Ignore this job if the file doesn't exist or has already been moved.
        if(!$this->model->fileExists) {
            return;
        }

        try {
            app(MediaService::class)->changeDisk($this->model, $this->disk);

            event(new MovedModelToDisk($this->model));
        }
        catch(CannotMoveModelException $e) {
            // Intentionally do nothing...
        }
    }
}
