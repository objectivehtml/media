<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Colorize extends ImageFilter implements FilterInterface {

    protected $red;

    protected $green;

    protected $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->colorize($this->red, $this->green, $this->blue);
        $image->save($model->path);
        $image->destroy();
    }

}
