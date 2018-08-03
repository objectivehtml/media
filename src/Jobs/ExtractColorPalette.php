<?php

namespace Objectivehtml\MediaManager\Jobs;

use Illuminate\Bus\Queueable;
use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;
use Objectivehtml\MediaManager\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use League\ColorExtractor\ColorExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExtractColorPalette implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Create a Palette instance from the model url
        $palette = Palette::fromFilename($this->model->path);

        // an extractor is built from a palette
        $extractor = new ColorExtractor($palette);

        $this->model->meta('primary_color', Color::fromIntToHex($extractor->extract(1)[0]));
        $this->model->save();
    }
}
