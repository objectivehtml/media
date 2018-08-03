<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Heighten extends ImageFilter implements FilterInterface {

    public $height;

    public $upsize;

    public function __construct($height, bool $upsize = true)
    {
        $this->height = $height;
        $this->upsize = $upsize;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->widen($this->height, function($constraint) {
            if($this->upsize) {
                $constraint->upsize();
            }
        });

        $image->save($model->path);
    }

}
