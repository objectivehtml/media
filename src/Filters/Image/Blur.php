<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Blur extends ImageFilter implements FilterInterface {

    protected $amount;

    public function __construct(int $amount = 50)
    {
        $this->amount = $amount;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->blur($this->amount);
        $image->save($model->path);
        $image->destroy();
    }

}
