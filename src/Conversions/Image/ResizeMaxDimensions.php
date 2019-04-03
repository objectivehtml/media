<?php

namespace Objectivehtml\Media\Conversions\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Filters\Image\Fit;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\conversions\Conversion;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class ResizeMaxDimensions extends Conversion implements ConversionInterface {

    use ApplyToImages;

    public $width;

    public $height;

    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);

        if($image->width() >= $this->width || $image->height() >= $this->height) {
            $image->fit($this->width, $this->height);
            $image->save($model->path);
            $image->destroy();
        }
    }

}
