<?php

namespace Objectivehtml\Media\Plugins;

use Exception;
use Carbon\Carbon;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToAudio;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Services\VideoService;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;

class AudioPlugin extends Plugin {

    use Applyable, ApplyToAudio;

    public function created(Model $model)
    {
        if(!$model->meta->get('taken_at')) {
            $tags = app(VideoService::class)->format($model->path)->get('tags');

            $model->meta('taken_at', isset($tags['creation_time']) ? Carbon::parse($tags['creation_time']) : null);
            $model->save();
        }
    }

    public function jobs(Model $model): array
    {
        return ConfigClassStrategy::map(app(MediaService::class)->config('audio.jobs', []), $model);
    }

    public function filters(Model $model): array
    {
        return ConfigClassStrategy::map(app(MediaService::class)->config('audio.filters', []));
    }

    public function conversions(Model $model): array
    {
        return ConfigClassStrategy::map(app(MediaService::class)->config('audio.conversions', []));
    }

    public function doesMeetRequirements(): bool
    {
        if(class_exists('FFMpeg\FFMpeg')) {
            try {
                $ffmpeg = \FFMpeg\FFMpeg::create(app(MediaService::class)->config('ffmpeg'));
            }
            catch(Exception $e) {
                return false;
            }
    
            return true;
        }

        return false;
    }

}
