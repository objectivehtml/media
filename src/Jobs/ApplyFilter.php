<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Objectivehtml\Media\Contracts\Filter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\StartedApplyingFilter;
use Objectivehtml\Media\Events\FinishedApplyingFilter;

class ApplyFilter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $filter;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model, Filter $filter)
    {
        $this->model = $model;
        $this->filter = $filter;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        event(new StartedApplyingFilter($this->model, $this->filter));

        $this->filter->apply($this->model);

        event(new FinishedApplyingFilter($this->model, $this->filter));
    }
}
