<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Objectivehtml\Media\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\MediaService;

class ApplyConversions implements ShouldQueue
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
        $conversions = app(MediaService::class)->conversions($this->model);

        // If no conversions, end this job gracefully.
        if(!$conversions->count()) {
            return;
        }

        $conversions->each(function($conversion) {
            $conversion->apply($this->model);
        });
    }
}
