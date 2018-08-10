<?php

namespace Objectivehtml\Media\Plugins;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Filters\Image\Fit;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Jobs\ExtractColorPalette;
use Objectivehtml\Media\Conversions\Image\Thumbnail;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function jobs(Model $model): array
    {
        return array_map(JobsConfigClassStrategy::make($model), app(MediaService::class)->config('image.jobs', []));
    }

    public function filters(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('image.filters', []));
    }

    public function conversions(Model $model): array
    {
        return array_map(ConfigClassStrategy::make(), app(MediaService::class)->config('image.conversions', []));
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
            $image = app(MediaService::class)->image($model->path)->orientate();

            try {
                if(!$model->exif &&
                    $model->fileExists &&
                    $exif = $image->exif()
                ) {
                    $model->meta('exif', $exif);
                }
            }
            catch(NotReadableException $e) {
                // If the file can't be read, then it has no exif data...
            }

            $image->save();

            $model->meta('width', $image->width());
            $model->meta('height', $image->height());
            $model->save();

            $image->destroy();
        }

        if(app(MediaService::class)->config('image.colors')) {
            if(($total = app(MediaService::class)->config('image.colors.total')) && !$model->meta('colors') && $model->fileExists) {
                ExtractColorPalette::dispatch($model, $total);
            }
        }
    }

}
