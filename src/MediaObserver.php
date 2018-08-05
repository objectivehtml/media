<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Jobs\RemoveModelFromDisk;

class MediaObserver
{

    public function saving(Model $model)
    {
        if($model->fileExists) {
            $model->size = app(MediaService::class)->storage()->disk($model->disk)->size($model->relative_path) ?: 0;
        }
    }

    public function created(Model $model)
    {
        if(is_null($model->getAttribute('directory'))) {
            $model->directory = app(MediaService::class)->directory($model, $model->resource() ? $model->resource()->directoryStrategy() : null);
            $model->save();
        }

        if(($resource = $model->resource()) && !$model->fileExists) {
            app(MediaService::class)->storage()->disk($model->disk)->put($model->relative_path, $resource->getResource());
        }
    }

    public function deleting(Model $model)
    {
        foreach($model->children as $child) {
            RemoveModelFromDisk::dispatch($child);
        }

        RemoveModelFromDisk::dispatch($model);
    }

}
