<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Pixelate extends ImageFilter implements FilterInterface {

    protected $size;

    public function __construct(int $size = 12)
    {
        $this->size = $size;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->pixelate($this->size);
        $image->save($model->path);
        $image->destroy();
    }

}
