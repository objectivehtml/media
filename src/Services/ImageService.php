<?php

namespace Objectivehtml\Media\Services;

use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as Img;

class ImageService extends Service {

    /**
     * Create an instance of an Image object.
     *
     * @param  mixed $image
     * @return Intervention\Image\Image
     */
    public function make($image): Image
    {
        return Img::make($image);
    }

    
}