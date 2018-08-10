<?php

namespace Objectivehtml\Media\Conversions\Video;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\ApplyToVideos;
use Objectivehtml\Media\Conversions\Conversion;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class ExtractFrames extends Conversion implements ConversionInterface {

    use ApplyToVideos;

    public $starting;

    public $interval;

    public function __construct(number $starting = null, number $interval = null)
    {
        $this->starting = $starting ?: app(MediaService::class)->config('video.extract_frames_starting_at', 0);
        $this->interval = $interval ?: app(MediaService::class)->config('video.extract_frames_interval', 0);
    }

    public function apply(Model $model)
    {
        if($this->interval) {
            for($x = $this->starting; $x < floor($model->meta->get('duration')); $x += $this->interval) {
                app(MediaService::class)->extractFrame($model, $x);
            }
        }
        else {
            app(MediaService::class)->extractFrame($model, $this->starting);
        }
    }

}
