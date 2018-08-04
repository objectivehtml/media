<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;
use Objectivehtml\Media\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use League\ColorExtractor\ColorExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        // Create a Palette instance from the model url
        $palette = Palette::fromFilename($this->model->path);

        if(count($colors = $palette->getMostUsedColors($this->total))) {
            $this->model->meta('colors', Color::fromIntToHex($color));
            $this->model->save();
        }

        /*
        // an extractor is built from a palette
        $extractor = new ColorExtractor($palette);

        $colors = array_map(function($color) {
            return Color::fromIntToHex($color);
        }, $extractor->extract($this->total));

        if(count($colors)) {
            $this->model->meta('colors', $colors);
            $this->model->save();
        }
        */
    }
}
