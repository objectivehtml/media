<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\MediaService;

trait ApplyToVideo {

    public function applyToMimes(): array
    {
        return app(MediaService::class)->config('video.mimes');
    }

    public function applyToExtensions(): array
    {
        return app(MediaService::class)->config('video.extensions');
    }

}
