<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToAudio;

class AudioPlugin extends Plugin {

    use Applyable, ApplyToAudio;

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
