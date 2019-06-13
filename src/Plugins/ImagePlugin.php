<?php

namespace Objectivehtml\Media\Plugins;

use Exception;
use Carbon\Carbon;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function jobs(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.jobs', []), $model);
    }

    public function filters(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.filters', []));
    }

    public function conversions(Model $model): array
    {
        return ConfigClassStrategy::map(app(ImageService::class)->config('image.conversions', []));
    }

    public function doesMeetRequirements(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

    public function created(Model $model)
    {
        if($model->fileExists) {
            $image = app(ImageService::class)
                ->make($model->path)
                ->orientate()
                ->save();

            if(!$model->meta->get('exif')) {
                try {
                    $model->meta('exif', $image->exif());
                }
                catch(NotReadableException $e) {
                    // If the file can't be read, then it has no exif data...
                    $model->meta('exif', null);
                }
            }

            if(!$model->meta->get('width')) {
                $model->meta('width', $image->width());
            }

            if(!$model->meta->get('height')) {
                $model->meta('height', $image->height());
            }

            if(!$model->meta->get('taken_at') && $model->exif) {
                try {
                    $model->taken_at = Carbon::parse($model->exif->DateTimeOriginal ?: $model->exif->DateTime);
                    $model->meta('taken_at', $model->taken_at);
                }
                catch(Exception $e) {
                    //
                }
            }
            
            $model->save();
                
            $image->destroy();
        }
    }

}
