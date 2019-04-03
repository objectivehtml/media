<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Contracts\Conversion;
use Objectivehtml\Media\Events\StartedApplyingConversion;
use Objectivehtml\Media\Events\FinishedApplyingConversion;

class ApplyConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $conversion;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model, Conversion $conversion)
    {
        $this->model = $model;
        $this->conversion = $conversion;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        event(new StartedApplyingConversion($this->model, $this->conversion));

        $this->conversion->apply($this->model);

        event(new FinishedApplyingConversion($this->model, $this->conversion));
    }
}
