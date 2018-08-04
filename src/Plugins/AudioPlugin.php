<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToAudio;
use Objectivehtml\Media\Jobs\PreserveOriginal;
use FFMpeg\Exception\ExecutableNotFoundException;

class AudioPlugin extends Plugin {

    use Applyable, ApplyToAudio;

    public function saving(Model $model)
    {
        if(!$this->doesApplyToModel($model)) {
            return;
        }
    }

    public function jobs(Model $model): array
    {
        return [
            new PreserveOriginal($model, $model->resource() ? $model->resource()->preserveOriginal() : app(MediaService::class)->config('audio.preserve'))
        ];
    }

    public function conversions(Model $model): array
    {
        return app(MediaService::class)->config('audio.conversions') ?: [];
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
