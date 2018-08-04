<?php

namespace Objectivehtml\Media\Resources;

use InvalidArgumentException;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Filters\Filters;
use Objectivehtml\Media\Support\Metable;
use Objectivehtml\Media\Support\Taggable;
use Objectivehtml\Media\Support\Filterable;
use Objectivehtml\Media\Support\Convertable;
use Objectivehtml\Media\Contracts\Convertable as ConvertableInterface;
use Objectivehtml\Media\Contracts\Filterable as FilterableInterface;
use Objectivehtml\Media\Contracts\Metable as MetableInterface;
use Objectivehtml\Media\Contracts\Taggable as TaggableInterface;
use Objectivehtml\Media\Contracts\StreamableResource as StreamableResourceInterface;

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
