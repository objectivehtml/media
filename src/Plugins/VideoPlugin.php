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

    public function saving(Model $model)
    {
        if($this->doesApplyToModel($model) && $model->fileExists) {
            $model->meta('width', $width = app(MediaService::class)->width($model->path));
            $model->meta('height', $height = app(MediaService::class)->height($model->path));
            $model->meta('duration', app(MediaService::class)->duration($model->path));
            $model->meta('aspect_ratio', app(MediaService::class)->aspectRatio($width, $height));
            $model->meta('bit_rate', app(MediaService::class)->bitRate($model->path));
        }
    }

    public function created(Model $model)
    {
        if($this->doesApplyToModel($model)) {
            if($model->isParent()) {
                app(MediaService::class)->extractFrame($model);
            }
        }
    }

    public function jobs(Model $model): array
    {
        return [
            new PreserveOriginal($model, $model->resource() ? $model->resource()->preserveOriginal() : app(MediaService::class)->config('video.preserve'))
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
