<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Objectivehtml\Media\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\MediaService;
use Intervention\Image\ImageManagerStatic as Image;

class ResizeMaxDimensions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $width;

    protected $height;

    /**
     * Create a new job instance.
     *
     * @param  Model  $model
     * @return void
     */
    public function __construct(Model $model, $width, $height)
    {
        $this->model = $model;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        $image = Image::make($this->model->path);

        $image->fit(
            $this->width ?: app(MediaService::class)->config('image.max_width'),
            $this->height ?: app(MediaService::class)->config('image.max_height')
        );

        $image->save($this->model->path);
        $image->destroy();
    }
}
