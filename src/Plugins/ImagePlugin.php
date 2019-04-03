<?php

namespace Objectivehtml\Media\Plugins;

use Carbon\Carbon;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Filters\Image\Fit;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Jobs\ExtractColorPalette;
use Objectivehtml\Media\Conversions\Image\Thumbnail;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function jobs(Model $model): array
    {
        return array_map(JobsConfigClassStrategy::make($model), app(ImageService::class)->config('image.jobs', []));
    }

    public function filters(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(ImageService::class)->config('image.filters', []));
    }

    public function conversions(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(ImageService::class)->config('image.conversions', []));
    }

    public function doesMeetRequirements(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

    public function created(Model $model)
    {
        if(!$this->doesApplyToModel($model)) {
            return;
        }

        if($model->fileExists) {
            $image = app(ImageService::class)->make($model->path)->orientate();
            $image->save();

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
                $model->taken_at = $model->exif->DateTimeOriginal ?: $model->exif->DateTime;
                $model->meta('taken_at', $model->taken_at);
            }
            
            $model->save();

            $image->destroy();
        }

        if(app(ImageService::class)->config('image.colors')) {
            if(($total = app(ImageService::class)->config('image.colors.total')) && !$model->meta('colors') && $model->fileExists) {
                ExtractColorPalette::dispatch($model, $total);
            }
        }
    }

}
