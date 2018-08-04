<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Objectivehtml\Media\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Exceptions\CannotPreserveOriginalException;

class PreserveOriginal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $preserveOriginal;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model, $preserveOriginal = true)
    {
        $this->model = $model;
        $this->preserveOriginal = $preserveOriginal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check to see if the original should be preserved and gracefully
        // continue if original should not be preserved. This may seem redundant
        // but it allows the job to be fired and start a chain, but elect
        // to ignore the actual preservation.
        if(!$this->preserveOriginal) {
            return;
        }

        try {
            app(MediaService::class)->preserveOriginal($this->model);
        }
        catch(CannotPreserveOriginalException $e) {
            //
        }
    }
}
