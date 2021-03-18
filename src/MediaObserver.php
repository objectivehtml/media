<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Jobs\RemoveFileFromDisk;

class MediaObserver
{

    public function saving(Model $model)
    {
        if(!$model->size && $model->fileExists) {
            $model->size = app(MediaService::class)
                ->storage()
                ->disk($model->disk)
                ->size($model->relative_path) ?: 0;
        }

        if($model->mime) {
            $model->tag(explode('/', $model->mime)[0]);
        }

        /*
        if($model->exists && !file_exists($model->path) && $resource = $model->resource()) {
            $model->disk = app(MediaService::class)->config('temp.disk');
            $model->ensureDirectoryExists();

            // Put the stream's contents in the model's temp file path where
            // the file can be processed locally and put back on the server.
            file_put_contents($model->path, $resource->stream());
        }
        */
    }

    public function saved(Model $model) {
        if($model->resource() && ($attachTo = $model->resource()->attachTo())) {
            app(MediaService::class)->attachTo($model, $attachTo);
        }
    }

    public function creating(Model $model)
    {
        if($model->temporary()) {
            $toDisk = app(MediaService::class)->config('disk');

            if($resource = $model->resource()) {
                $toDisk = $resource->disk() ?: $toDisk;
            }

            $model->meta('move_to', $toDisk);
        }
    }

    public function created(Model $model)
    {
        $strategy =  $model->resource() ?
            $model->resource()->directoryStrategy() : null;
        
        if(is_null($model->getAttribute('directory'))) {
            $model->directory = app(MediaService::class)->directory(
                $model, $strategy
            );

            $model->save();
        }

        if(($resource = $model->resource()) && !$model->fileExists) {
            app(MediaService::class)
                ->storage()
                ->disk($model->disk)
                ->put($model->relative_path, $resource->stream(), 'public');
        }
    }

    public function deleting(Model $model)
    {
        foreach($model->children as $child) {
            RemoveFileFromDisk::dispatch($model->disk, $model->relative_path);
        }

        RemoveFileFromDisk::dispatch($model->disk, $model->relative_path);
    }

}
