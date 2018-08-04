<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Contrast extends ImageFilter implements FilterInterface {

    protected $level;

    public function __construct(int $level)
    {
        $this->level = $level;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->contrast($this->level);
        $image->save($model->path);
        $image->destroy();
    }

}
