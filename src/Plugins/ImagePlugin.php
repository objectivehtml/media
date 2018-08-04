<?php

namespace Objectivehtml\Media\Plugins;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToImages;
use Objectivehtml\Media\Jobs\PreserveOriginal;
use Objectivehtml\Media\Jobs\ExtractColorPalette;
use Objectivehtml\Media\Jobs\ResizeMaxDimensions;
use Objectivehtml\Media\Conversions\Image\Thumbnail;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function created(Model $model)
    {
        if(!$this->doesApplyToModel($model)) {
            return;
        }

        if($model->fileExists) {
            $image = app(MediaService::class)->image($model->path);
            $model->meta('width', $image->width());
            $model->meta('height', $image->height());
            $model->save();
            $image->destroy();
        }

        if(($total = app(MediaService::class)->config('image.colors.total')) && !$model->meta('colors') && $model->fileExists) {
            ExtractColorPalette::dispatch($model, $total);
        }
    }

    public function jobs(Model $model): array
    {
        $resource = $model->resource();

        return [
            new PreserveOriginal($model, $resource ? $resource->preserveOriginal() : config('preserve_original')),
            new ResizeMaxDimensions(
                $model,
                app(MediaService::class)->config('image.max_width'),
                app(MediaService::class)->config('image.max_height')
            )
        ];
    }

    public function conversions(Model $model): array
    {
        return app(MediaService::class)->config('image.conversions') ?: [];
    }

    public function doesMeetRequirements(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

}
