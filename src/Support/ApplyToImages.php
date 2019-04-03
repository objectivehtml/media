<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\Services\MediaService;

trait ApplyToImages {

    public function applyToMimes(): array
    {
        return app(MediaService::class)->config('image.mimes');
    }

    public function applyToExtensions(): array
    {
        return app(MediaService::class)->config('image.extensions');
    }

}
