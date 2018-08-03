<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Brightness extends ImageFilter implements FilterInterface {

    protected $level;

    public function __construct(int $level)
    {
        $this->level = $level;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->brightness($this->level);
        $image->save($model->path);
    }

}
