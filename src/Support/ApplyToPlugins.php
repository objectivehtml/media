<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\MediaService;

trait ApplyToPlugins {

    public function applyToMimes(): array
    {
        return array_merge(
            app(MediaService::class)->config('image.mimes'),
            app(MediaService::class)->config('video.mimes'),
            app(MediaService::class)->config('audio.mimes')
        );
    }

    public function applyToExtensions(): array
    {
        return array_merge(
            app(MediaService::class)->config('image.extensions'),
            app(MediaService::class)->config('video.extensions'),
            app(MediaService::class)->config('audio.extensions')
        );
    }

}
