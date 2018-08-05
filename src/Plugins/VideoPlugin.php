<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToVideo;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;

class VideoPlugin extends Plugin {

    use Applyable, ApplyToVideo;

    public function created(Model $model)
    {
        if($this->doesApplyToModel($model) && $model->fileExists) {
            if(!$model->meta->get('width') && !$model->meta->get('height')) {
                $model->meta('width', $width = app(MediaService::class)->width($model->path));
                $model->meta('height', $height = app(MediaService::class)->height($model->path));
                $model->meta('aspect_ratio', app(MediaService::class)->aspectRatio($width, $height));
            }
            if(!$model->meta->get('duration')) {
                $model->meta('duration', app(MediaService::class)->duration($model->path));
            }
            if(!$model->meta->get('bit_rate')) {
                $model->meta('bit_rate', app(MediaService::class)->bitRate($model->path));
            }

            $model->save();

            if(app(MediaService::class)->config('sync_extract_first_frame') && $model->isParent()) {
                app(MediaService::class)->extractFrame($model);
            }
        }
    }

    public function jobs(Model $model): array
    {
        return array_map(JobsConfigClassStrategy::make($model), app(MediaService::class)->config('video.jobs') ?: []);;
    }

    public function filters(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('video.filters') ?: []);
    }

    public function conversions(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('video.conversions') ?: []);
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
