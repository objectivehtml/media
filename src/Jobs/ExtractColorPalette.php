<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use League\ColorExtractor\ColorExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Services\ImageService;
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
    public function __construct(Model $model, $total = null)
    {
        $this->model = $model;
        $this->total = $total ?: $model->meta->get('extract_colors', app(ImageService::class)->config('image.colors.total'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->model->fileExists) {
            return;
        }

        $maxWidth = app(ImageService::class)->config('image.colors.max_width', 600);
        $maxHidth = app(ImageService::class)->config('image.colors.max_height', 600);

        $image = app(ImageService::class)
            ->make($this->model->path)
            ->fit(
                min($this->model->width ?: $maxWidth, $maxWidth),
                min($this->model->height ?: $maxHidth, $maxHidth)
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
