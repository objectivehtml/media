<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

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
        $image = app(ImageService::class)->make($model->path);
        $image->rotate($this->angle);
        $image->save($model->path);
        $image->destroy();
    }

}
