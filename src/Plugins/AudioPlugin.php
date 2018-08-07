<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToAudio;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;

class AudioPlugin extends Plugin {

    use Applyable, ApplyToAudio;

    public function jobs(Model $model): array
    {
        return array_map(JobsConfigClassStrategy::make($model), app(MediaService::class)->config('audio.jobs', []));
    }

    public function filters(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('audio.filters', []));
    }

    public function conversions(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('audio.conversions', []));
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
