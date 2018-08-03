<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Crop extends ImageFilter implements FilterInterface {

    public $width;

    public $height;

    public $x;

    public $y;

    public function __construct($width, $height, $x = null, $y = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->x = $x;
        $this->y = $y;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->crop($this->width, $this->height, $this->x, $this->y);
        $image->save($model->path);
    }

}
