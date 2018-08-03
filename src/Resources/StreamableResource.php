<?php

namespace Objectivehtml\MediaManager\Resources;

use InvalidArgumentException;
use Objectivehtml\MediaManager\Model;
use Objectivehtml\MediaManager\MediaService;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\MediaManager\Filters\Filters;
use Objectivehtml\MediaManager\Support\Metable;
use Objectivehtml\MediaManager\Support\Taggable;
use Objectivehtml\MediaManager\Support\Filterable;
use Objectivehtml\MediaManager\Support\Convertable;
use Objectivehtml\MediaManager\Contracts\Convertable as ConvertableInterface;
use Objectivehtml\MediaManager\Contracts\Filterable as FilterableInterface;
use Objectivehtml\MediaManager\Contracts\Metable as MetableInterface;
use Objectivehtml\MediaManager\Contracts\Taggable as TaggableInterface;
use Objectivehtml\MediaManager\Contracts\StreamableResource as StreamableResourceInterface;

abstract class StreamableResource implements StreamableResourceInterface, ConvertableInterface, FilterableInterface, MetableInterface, TaggableInterface {

    use Convertable, Filterable, Metable, Taggable;

    protected $context;

    protected $directory;

    protected $directoryStrategy;

    protected $disk;

    protected $meta;

    protected $preserveOriginal = true;

    public function __call(string $key, array $args = [])
    {
        if(!property_exists($this, $key)) {
            throw new InvalidArgumentException('Method ' . $key . '() not exists');
        }

        if(isset($args[0])) {
            $this->$key = $args[0];

            return $this;
        }
        else {
            return $this->$key;
        }
    }

    public function directoryStrategy($strategy = null)
    {
        if(is_string($strategy) && class_exists($strategy)) {
            $strategy = $strategy::make();
        }

        if($strategy instanceof DirectoryStrategyInterface) {
            $this->directoryStrategy = $strategy;

            return $this;
        }
        else if(is_callable($strategy)) {
            $this->directoryStrategy = $strategy;

            return $this;
        }
        else if($strategy) {
            throw new InvalidArgumentException('The first argument must be callable or a DirectoryStrategy.');
        }

        return $this->directoryStrategy;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function storage(): Factory
    {
        return app(MediaService::class)->storage();
    }

    public function model(array $attributes = []): Model
    {
        $model = app(MediaService::class)->model(array_merge([
            'context' => $this->context
        ], $attributes), $this);

        $model->resource($this);

        return $model;
    }

    public function save(array $attributes = []): Model
    {
        $model = app(MediaService::class)->save($this, $attributes);
        $model->resource($this);

        return $model;
    }

}
