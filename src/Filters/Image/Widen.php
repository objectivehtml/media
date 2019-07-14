<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Widen extends ImageFilter implements FilterInterface {

    public $width;

    public $upsize;

    public function __construct($width, bool $upsize = true)
    {
        $this->width = $width;
        $this->upsize = $upsize;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->widen($this->width, function($constraint) {
            if($this->upsize) {
                $constraint->upsize();
            }
        });

        $image->save($model->path);
        $image->destroy();
    }

}
