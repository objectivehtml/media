<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Pixelate extends ImageFilter implements FilterInterface {

    protected $size;

    public function __construct(int $size = 12)
    {
        $this->size = $size;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->pixelate($this->size);
        $image->save($model->path);
    }

}
