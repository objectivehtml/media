<?php

namespace Objectivehtml\Media\Filters\Image;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;

class Fit extends ImageFilter implements FilterInterface {

    public $width;

    public $height;

    public $upsize;

    public function __construct($width, $height, bool $upsize = true)
    {
        $this->width = $width;
        $this->height = $height;
        $this->upsize = $upsize;
    }

    public function apply(Model $model)
    {
        $image = app(ImageService::class)->make($model->path);
        $image->fit($this->width, $this->height, function($constraint) {
            if($this->upsize) {
                $constraint->upsize();
            }
        });

        $image->save($model->path);
        $image->destroy();
    }

}
