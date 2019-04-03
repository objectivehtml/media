<?php

namespace Objectivehtml\Media\Resources;

use InvalidArgumentException;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Services\MediaService;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
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

    protected $directoryStrategy;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function __call($key, $args)
    {
        if(isset($args[0])) {
            return $this->meta($key, $args[0]);
        }

        return $this->meta($key);
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

    public function model(array $attributes = null): Model
    {
        return app(MediaService::class)->model(array_merge([
            'context' => $this->context()
        ], $attributes ?: []), $this);
    }

    public function save(array $attributes = []): Model
    {
        return app(MediaService::class)->save($attributes, $this);
    }

}
