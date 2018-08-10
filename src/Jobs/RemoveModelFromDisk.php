<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\RemovedModelFromDisk;

class RemoveModelFromDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model)
    {
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
        app(MediaService::class)
            ->storage()
            ->disk($this->model->disk)
            ->delete($this->model->relative_path);

        if(app(MediaService::class)->config('delete_directories')) {
            if(!count(app(MediaService::class)->storage()->disk($this->model->disk)->files($this->model->directory))) {
                app(MediaService::class)->storage()->disk($this->model->disk)->deleteDirectory($this->model->directory);
            }
        }

        event(new RemovedModelFromDisk($this->model));
    }
}
