<?php

namespace Objectivehtml\MediaManager\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\MediaManager\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\MediaManager\MediaService;

class ExtractFrames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $start;

    protected $interval;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, $start = 0, $interval = 30)
    {
        $this->model = $model;
        $this->start = $start;
        $this->interval = $interval;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        for($x = $this->start; $x < floor($this->model->meta->get('duration')); $x += $this->interval) {
            app(MediaService::class)->extractFrame($this->model, $x);
        }
    }
}
