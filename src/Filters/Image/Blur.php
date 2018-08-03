<?php

namespace Objectivehtml\MediaManager\Filters\Image;

use Objectivehtml\MediaManager\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Blur extends ImageFilter implements FilterInterface {

    protected $amount;

    public function __construct(int $amount = 50)
    {
        $this->amount = $amount;
    }

    public function apply(Model $model)
    {
        $image = Image::make($model->path);
        $image->blur($this->amount);
        $image->save($model->path);
    }

}
