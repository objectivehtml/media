<?php

namespace Objectivehtml\Media\Plugins;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Jobs\GeocodeModel;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Services\MediaService;

class GeocoderPlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function jobs(Model $model): array
    {
        return [
            new GeocodeModel($model)
        ];
    }

    public function doesMeetRequirements(): bool
    {
        return !!app(MediaService::class)->config('geocoder.api_key');
    }

}
