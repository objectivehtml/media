<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Crop extends ImageFilter implements FilterInterface {

    public $width;

    public $height;

    public $x;

    public $y;

    public function __construct($width, $height, $x = null, $y = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->x = $x;
        $this->y = $y;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->crop($this->width, $this->height, $this->x, $this->y);
        $image->save($model->path);
        $image->destroy();
    }

}
