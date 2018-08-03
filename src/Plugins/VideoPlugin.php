<?php

namespace Objectivehtml\MediaManager\Plugins;

use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Objectivehtml\MediaManager\MediaService;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Support\ApplyToVideo;

class VideoPlugin extends Plugin {

    use Applyable, ApplyToVideo;

    public function created(Model $model)
    {
        if($this->doesApplyToModel($model)) {
            if($model->isParent()) {
                app(MediaService::class)->extractFrame($model);
            }

            if($model->fileExists) {
                $model->meta('width', $width = app(MediaService::class)->width($model->path));
                $model->meta('height', $height = app(MediaService::class)->height($model->path));
                $model->meta('duration', app(MediaService::class)->duration($model->path));
                $model->meta('aspect_ratio', app(MediaService::class)->aspectRatio($width, $height));
                $model->meta('bit_rate', app(MediaService::class)->bitRate($model->path));
                $model->save();
            }
        }
    }

    public function conversions(Model $model): array
    {
        return app(MediaService::class)->config('video.conversions') ?: [];
    }

    public function doesMeetRequirements(): bool
    {
        try {
            $ffmpeg = FFMpeg::create(app(MediaService::class)->config('ffmpeg'));
        }
        catch(ExecutableNotFoundException $e) {
            return false;
        }

        return true;
    }

}
