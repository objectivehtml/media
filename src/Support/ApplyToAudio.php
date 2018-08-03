<?php

namespace Objectivehtml\MediaManager\Support;

use Objectivehtml\MediaManager\MediaService;

trait ApplyToAudio {

    public function applyToMimes(): array
    {
        return app(MediaService::class)->config('audio.mimes');
    }

    public function applyToExtensions(): array
    {
        return app(MediaService::class)->config('audio.extensions');
    }

}
