<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Orientate extends ImageFilter implements FilterInterface {

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->orientate();
        $image->save($model->path);
        $image->destroy();
    }

}
