<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Resize extends ImageFilter implements FilterInterface {

    public $width;

    public $height;

    public $aspectRatio;

    public $upsize;

    public function __construct($width, $height, bool $aspectRatio = true, bool $upsize = true)
    {
        $this->width = $width;
        $this->height = $height;
        $this->aspectRatio = $aspectRatio;
        $this->upsize = $upsize;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->resize($this->width, $this->height, function($constraint) {
            if($this->aspectRatio) {
                $constraint->aspectRatio();
            }
            if($this->upsize) {
                $constraint->upsize();
            }
        });

        $image->save($model->path);
    }

}
