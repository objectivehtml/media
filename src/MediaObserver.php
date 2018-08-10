<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Jobs\MoveModelToDisk;
use Objectivehtml\Media\Jobs\RemoveModelFromDisk;

class MediaObserver
{

    public function saving(Model $model)
    {
        if(!$model->size && $model->fileExists) {
            $model->size = app(MediaService::class)->storage()->disk($model->disk)->size($model->relative_path) ?: 0;
        }

        if($model->mime) {
            $model->tag(explode('/', $model->mime)[0]);
        }
    }

    public function saved(Model $model) {
        if($model->resource() && ($attachTo = $model->resource()->attachTo())) {
            app(MediaService::class)->attachTo($model, $attachTo);
        }

        if($model->shouldChangeDisk()) {
            $toDisk = $model->meta->get('move_to') ?: app(MediaService::class)->config('disk');

            MoveModelToDisk::withChain(
                $model->children()
                    ->disk($model->disk)
                    ->ready()
                    ->get()
                    ->map(function($child) use ($toDisk) {
                        return new MoveModelToDisk($child, $toDisk);
                    })
            )->dispatch($model, $toDisk);
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
        if(is_null($model->getAttribute('directory'))) {
            $model->directory = app(MediaService::class)->directory($model, $model->resource() ? $model->resource()->directoryStrategy() : null);
            $model->save();
        }

        if(($resource = $model->resource()) && !$model->fileExists) {
            app(MediaService::class)
                ->storage()
                ->disk($model->disk)
                ->put($model->relative_path, $resource->getResource(), 'public');
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
