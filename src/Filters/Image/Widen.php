<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Widen extends ImageFilter implements FilterInterface {

    public $width;

    public $upsize;

    public function __construct($width, bool $upsize = true)
    {
        $this->width = $width;
        $this->upsize = $upsize;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->widen($this->width, function($constraint) {
            if($this->upsize) {
                $constraint->upsize();
            }
        });

        $image->save($model->path);
    }

}
