<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Greyscale extends ImageFilter implements FilterInterface {

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->greyscale();
        $image->save($model->path);
    }

}
