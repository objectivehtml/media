<?php

namespace Objectivehtml\Media;

use Closure;
use Exception;
use Objectivehtml\Media\TemporaryModel;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Resources\FileResource;
use Objectivehtml\Media\Resources\RemoteResource;
use Objectivehtml\Media\Contracts\Resource;

class TemporaryFile {

    public function __construct(TemporaryModel $model = null, ?Closure $callback)
    {
        try {
            $callback($model);

            $model->delete();
        }
        catch(Exception $e) {
            // Catch all errors and delete the temp model before throwing the
            // error if the model still exists.
            $model->delete();

            throw $e;
        }
    }
    
    public static function make(Model $model, Closure $callback)
    {
        if(file_exists($model->path)) {
            $resource = new FileResource(new File($model->path));
            
            return new static(static::model($model, $resource), $callback);
        }
        else if($model->resource()) {
            $resource = $model->resource();
        }
        else if(!file_exists($model) && $model->fileExists) {
            $resource = new RemoteResource(
                file_exists($model->path) ? $model->path : url($model->url)
            );
        }
        else if(!$model->resource()) {
            throw new InvalidResourceException;
        }

        return new static(static::model($model, $resource), $callback);
    }

    public static function model(Model $parent, Resource $resource)
    {
        $model = TemporaryModel::make([
            'extension' => $resource->extension(),
            'mime' => $resource->mime(),
            'size' => $resource->size()
        ]);

        $model->parent()->associate($parent);
        $model->resource($resource);
        $model->save();

        return $model;
    }

}
