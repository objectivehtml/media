<?php

namespace Objectivehtml\MediaManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\MediaManager\MediaService;
use Intervention\Image\ImageManagerStatic as Image;

class ResizeMaxDimensions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

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
        $image = Image::make(
            $path = app(MediaService::class)->storage()->disk($this->model->disk)->path($this->model->relative_path)
        );

        $maxWidth = app(MediaService::class)->config('image.max_width');
        $maxHeight = app(MediaService::class)->config('image.max_height');

        if($image->width() > $maxWidth) {
            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        else if($image->height() > $maxHeight) {
            $image->resize(null, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        app(MediaService::class)
            ->storage()
            ->disk($this->model->disk)
            ->put($this->model->relative_path, $image->encode());
    }
}
