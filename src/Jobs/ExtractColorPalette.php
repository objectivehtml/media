<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;
use Objectivehtml\Media\MediaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use League\ColorExtractor\ColorExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\ExtractedColorPalette;

class ExtractColorPalette implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $total;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, $total = 1)
    {
        $this->model = $model;
        $this->total = $total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = app(MediaService::class)->image($this->model->path);

        $image->fit(
            min($this->model->width, app(MediaService::class)->config('image.colors.max_width', 600)),
            min($this->model->height, app(MediaService::class)->config('image.colors.max_height', 600))
        );

        // Create a Palette instance from the model url
        $palette = Palette::fromGd(imagecreatefromstring($image->stream()->getContents()));

        // an extractor is built from a palette
        $extractor = new ColorExtractor($palette);

        $colors = array_map(function($color) {
            return Color::fromIntToHex($color);
        }, $extractor->extract($this->total));

        if(count($colors)) {
            $this->model->meta('colors', $colors);
            $this->model->save();
        }

        event(new ExtractedColorPalette($this->model));
    }
}
