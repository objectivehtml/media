<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Rotate extends ImageFilter implements FilterInterface {

    protected $angle;

    protected $bgcolor;

    public function __construct($angle, $bgcolor)
    {
        $this->angle = $angle;
        $this->bgcolor = $bgcolor;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->rotate($this->angle);
        $image->save($model->path);
    }

}
