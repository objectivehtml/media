<?php

namespace Objectivehtml\Media\Plugins;

use Exception;
use Carbon\Carbon;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function jobs(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.jobs', []), $model);
    }

    public function filters(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.filters', []));
    }

    public function conversions(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.conversions', []));
    }

    public function doesMeetRequirements(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

    public function saving(Model $model)
    {
        app(ImageService::class)->updateMetaData($model);
    }

}
