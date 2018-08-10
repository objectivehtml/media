<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Orientate extends ImageFilter implements FilterInterface {

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->orientate();
        $image->save($model->path);
        $image->destroy();
    }

}
