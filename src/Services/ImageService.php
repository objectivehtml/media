<?php

namespace Objectivehtml\Media\Services;

use Exception;
use Carbon\Carbon;
use Intervention\Image\Image;
use Objectivehtml\Media\Model;
use Intervention\Image\Facades\Image as Img;

class ImageService extends Service {

    /**
     * Create an instance of an Image object.
     *
     * @param  mixed $image
     * @return Intervention\Image\Image
     */
    public function make($image): Image
    {
        return Img::make($image)
            ->orientate()
            ->save();
    }

    public function takenAt(Model $model): ?Carbon
    {
        if($model->exif && ($model->exif->DateTimeOriginal || $model->exif->DateTime)) {
            return Carbon::parse($model->exif->DateTimeOriginal ?: $model->exif->DateTime);
        }

        return null;
    }

    public function updateMetaData(Model $model)
    {
        try {
            $image = app(ImageService::class)->make($model->path);

            if(!$model->exif) {
                $model->exif = $image->exif();
            }
            
            if($model->width !== $image->width()) {
                $model->width = $image->width();
            }
            
            if($model->height !== $image->height()) {
                $model->height = $image->height();
            }

            $image->destroy();
        }
        catch(Exception $e) {
            // Do nothing...
        }

        if($takenAt = app(ImageService::class)->takenAt($model)) {
            $model->taken_at = $takenAt;
        }
    }
    
}