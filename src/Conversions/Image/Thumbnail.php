<?php

namespace Objectivehtml\Media\Conversions\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\MediaService;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Filters\Image\Fit;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Conversions\Conversion;
use Objectivehtml\Media\Contracts\StreamableResource;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class Thumbnail extends Conversion implements ConversionInterface {

    use ApplyToImages;

    public $width;

    public $height;

    public $x;

    public $y;

    public function __construct($width = null, $height = null, $x = null, $y = null)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function apply(Model $model)
    {
        app(MediaService::class)
            ->resource($model->path)
            ->filters([
                new Fit($this->width, $this->height)
            ])
            ->context('thumbnail')
            ->convert($model, [
                'extension' => $model->extension
            ]);
    }

}
