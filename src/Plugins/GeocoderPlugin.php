<?php

namespace Objectivehtml\Media\Plugins;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Jobs\GeocodeModel;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToImages;

class GeocoderPlugin extends Plugin {

    use Applyable, ApplyToImages;

    protected $geocoder;

    public function saved(Model $model)
    {
        // Do not geocode if the model doesn't apply, if the model already has
        // geocoder data the file doesn't exist, if the model has no ExifData,
        // or there is no latitude or longitude.
        if(!$this->doesApplyToModel($model) ||
            $model->meta->get('geocoder') ||
            !$model->fileExists ||
            !$model->exif ||
            !$model->exif->latitude ||
            !$model->exif->longitude) {
            return;
        }

        GeocodeModel::dispatch($model);
    }

}
