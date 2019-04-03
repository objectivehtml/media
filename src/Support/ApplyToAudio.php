<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\Services\MediaService;

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
