<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\FinishedProcessingMedia;

class FinishProcessingMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->model->shouldChangeDisk()) {
            collect([
                $parent = $this->model->parent ?: $this->model
            ])
            ->concat($parent->children()->ready()->get())
            ->each(function($model) {
                MoveModelToDisk::dispatch($model, (
                    $model->meta->get('move_to') ?: app(MediaService::class)->config('disk')
                ));
            });
        }

        event(new FinishedProcessingMedia($this->model));
    }
}
