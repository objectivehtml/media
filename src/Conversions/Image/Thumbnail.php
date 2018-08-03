<?php

namespace Objectivehtml\MediaManager\Conversions\Image;

use Objectivehtml\MediaManager\Model;
use Objectivehtml\MediaManager\MediaService;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Filters\Image\Resize;
use Objectivehtml\MediaManager\Support\ApplyToImages;
use Objectivehtml\MediaManager\Conversions\Conversion;
use Objectivehtml\MediaManager\Contracts\StreamableResource;
use Objectivehtml\MediaManager\Contracts\Conversion as ConversionInterface;

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
        $file = app(MediaService::class)->storage()->disk($model->disk)->get($model->relative_path);

        $image = Image::make($file)->crop($this->width, $this->height, $this->x, $this->y);

        $child = app(MediaService::class)
            ->resource($image)
            ->filter(new Resize(200, 200, true, true))
            ->context('thumbnail')
            ->convert($model, [
                'size' => $image->filesize()
            ]);
    }

}
