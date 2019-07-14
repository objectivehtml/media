<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Contrast extends ImageFilter implements FilterInterface {

    protected $level;

    public function __construct(int $level)
    {
        $this->level = $level;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->contrast($this->level);
        $image->save($model->path);
        $image->destroy();
    }

}
