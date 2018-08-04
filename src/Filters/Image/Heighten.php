<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

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
        $image->destroy();
    }

}
