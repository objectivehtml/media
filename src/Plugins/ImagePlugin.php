<?php

namespace Objectivehtml\MediaManager\Plugins;

use Illuminate\Database\Eloquent\Model;
use Objectivehtml\MediaManager\MediaService;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Support\ApplyToImages;
use Objectivehtml\MediaManager\Jobs\ExtractColorPalette;
use Objectivehtml\MediaManager\Jobs\ResizeMaxDimensions;
use Objectivehtml\MediaManager\Conversions\Image\Thumbnail;

class ImagePlugin extends Plugin {

    use Applyable, ApplyToImages;

    public function created(Model $model)
    {
        if(!$this->doesApplyToModel($model)) {
            return;
        }

        if(($total = app(MediaService::class)->config('image.extract_colors')) && !$model->meta('colors') && $model->fileExists) {
            ExtractColorPalette::dispatch($model, $total);
        }
    }

    public function jobs(Model $model): array
    {
        return [
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
