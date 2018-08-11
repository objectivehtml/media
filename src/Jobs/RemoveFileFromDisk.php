<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\RemovedFileFromDisk;

class RemoveFileFromDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $disk;

    protected $relativePath;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct($disk, $relativePath)
    {
        $this->disk = $disk;
        $this->relativePath = $relativePath;
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
            ->disk($this->disk)
            ->delete($this->relativePath);

        $directory = dirname($this->relativePath);

        if(app(MediaService::class)->config('delete_directories')) {
            if(!count(app(MediaService::class)->storage()->disk($this->disk)->files($directory))) {
                app(MediaService::class)->storage()->disk($this->disk)->deleteDirectory($directory);
            }
        }

        event(new RemovedFileFromDisk($this->disk, $this->relativePath));
    }
}
