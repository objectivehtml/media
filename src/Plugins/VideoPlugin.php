<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToVideo;
use Objectivehtml\Media\Jobs\PreserveOriginal;
use FFMpeg\Exception\ExecutableNotFoundException;

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

    public function jobs(Model $model): array
    {
        $resource = $model->resource();

        return [
            new PreserveOriginal($model, $resource ? $resource->preserveOriginal() : config('preserve_original'))
        ];
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
