<?php

namespace Objectivehtml\MediaManager\Plugins;

use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Objectivehtml\MediaManager\MediaService;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Support\ApplyToAudio;

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
