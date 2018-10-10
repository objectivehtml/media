<?php

namespace Objectivehtml\Media\Conversions;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Filters\Image\Fit;
use Objectivehtml\Media\Support\ApplyToPlugins;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class PreserveOriginal extends Conversion implements ConversionInterface {

    use ApplyToPlugins;

    public function apply(Model $model)
    {
        if($model->meta->get('preserveOriginal') !== false) {
            app(MediaService::class)->preserveOriginal($model);
        }
    }

}
