<?php

namespace Objectivehtml\Media\Services;

use Illuminate\Http\Request;
use Intervention\Image\Image;
use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Resources\FileResource;
use Objectivehtml\Media\Resources\ImageResource;
use Objectivehtml\Media\Resources\RemoteResource;
use Objectivehtml\Media\Contracts\StreamableResource;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Exceptions\InvalidResourceException;
use Objectivehtml\Media\Contracts\Strategy as StrategyInterface;
use Objectivehtml\Media\Exceptions\CannotPreserveOriginalException;
use Objectivehtml\Media\Resources\TmpResource;

class MediaService extends Service {

    protected $plugins;

    public function __construct(Factory $filesystem, array $config = [])
    {
        parent::__construct($filesystem, $config);

        $this->plugins = collect($this->config('plugins'))
            ->map(function($class) {
                return new $class();
            });
    }

    /**
     * Attach an instance of Objectivehtml\Media\Model to another model.
     *
     * @param  Objectivehtml\Media\Model  $model
     * @param  Illuminate\Database\Eloquent\Model $attachTo
     * @return void
     */
    public function attachTo(Model $model, \Illuminate\Database\Eloquent\Model $attachTo)
    {
        if(!$attachTo->media()->get()->contains($model)) {
            $attachTo->media()->attach($model);
        }
    }
    
    /**
     * Attempst to convert the given data into a Objectivehtml\Media\Model
     * instance.
     *
     * @param  array  $data
     * @return mixed
     */
    public function translateIntoModel($data)
    {
        $key = $this->keyName();

        if($data instanceof Model) {
            return $data;
        }
        else if(is_numeric($data)) {
            return $this->config('model', Model::class)::find($data);
        }
        else if($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        if($data instanceof Jsonable) {
            $data = (array) $data->toJson();
        }
        else if(!is_array($data)) {
            $data = (array) $data;
        }

        if(!isset($data[$key])) {
            return null;
        }

        return $this->config('model', Model::class)::find($data[$key]);
    }

    /**
     * Get the directory of a model using the specified directory strategry.
     * If no strategy is specified, the default will be used.
     *
     * @param  Objectivehtml\Media\Model  $model
     * @param  Objectivehtml\Media\Contracts\Strategy $strategy
     * @return string
     */
    public function directory(Model $model, StrategyInterface $strategy = null): string
    {
        if(!$strategy) {
            $strategy = $this->directoryStrategy($model);
        }

        return rtrim($strategy($model), '/');
    }


    /**
     * Get the directory strategory.
     *
     * @return Objectivehtml\Media\Contracts\Strategy
     */
    public function directoryStrategy(): StrategyInterface
    {
        return $this->config('strategies.directory')::make();
    }

    /**
     * Get the extension from a given path.
     *
     * @param  mixed $path
     * @return mixed
     */
    public function extension(?string $path): ?string
    {
        return pathinfo($path, PATHINFO_EXTENSION) ?: null;
    }

    public function filename(Model $model, $strategy = null): ?string
    {
        if(!$strategy) {
            $strategy = $this->filenameStrategy($model);
        }

        return rtrim($strategy($model), '/');
    }

    public function filenameStrategy(): StrategyInterface
    {
        return $this->config('strategies.filename')::make();
    }

    /**
     * Get the models from the request.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection;
     */
    public function getModelsFromRequest(Request $request, $keys = null): Collection
    {
        return collect($keys ?: $this->config('request'))
            ->map(function($key) {
                return request()->input($key);
            })
            ->flatten(1)
            ->map(function($item) {
                return $this->translateIntoModel($item);
            })
            ->filter();
    }

    /**
     * Get the name of the primary key from a given model. If no model is
     * supplied, then the model defined in th config is used.
     *
     * @param  Objectivehtml\Media\Model $model
     * @return string
     */
    public function keyName(Model $model = null):string
    {
        if(!$model) {
            $model = $this->config('model', Model::class)::make();
        }

        return (new $model())->getKeyName();
    }

    public function matching(Model $model, $strategy = null): ?Model
    {
        if(!$strategy) {
            $strategy = $this->matchingStrategy($model);
        }

        if($matching = $strategy($model)) {
            return $matching->parent ?: $matching;
        }

        return null;
    }

    public function matchingStrategy(): StrategyInterface
    {
        return $this->config('strategies.matching')::make();
    }

    /**
     * Create an instance of a Objectivehtml\Media\Model.
     *
     * @param  array $attributes
     * @param  Objectivehtml\Media\Contracts\StreamableResource $resource
     * @return Objectivehtml\Media\Model
     */
    public function model(array $attributes = [], StreamableResource $resource = null): Model
    {
        $model = $this->config('model')::make(array_merge(array_filter([
            'disk' => $resource->disk() ?: $this->config('temp.disk'),
            'context' => $resource ? $resource->context() : null,
            'directory' => $resource ? $resource->directory() : null,
            'orig_filename' => $resource ? $resource->originalFilename() : null,
            'extension' => $resource ? $resource->extension() : null,
            'mime' => $resource ? $resource->mime() : null,
            'size' => $resource ? $resource->size() : null,
            'filters' => $resource ? $resource->filters() : null,
            'conversions' => $resource ? $resource->conversions() : null,
            'meta' => $resource ? $resource->meta() : null,
            'tags' => $resource ? $resource->tags() : null
        ]), $attributes));

        if($resource) {
            $model->resource($resource);
        }

        if($matching = $this->matching($model)) {
            if($resource && ($attachTo = $resource->attachTo())) {
                $this->attachTo($matching, $attachTo);
            }

            return $matching;
        }

        return $model;
    }

    /**
     * Copy the file and preserve it as the original.
     *
     * @param  Objectivehtml\Media\Model $model
     * @return Objectivehtml\Media\Model
     */
    public function preserveOriginal(Model $model)
    {
        if($model->children()->context('original')->count()) {
            throw new CannotPreserveOriginalException('Original already exists.');
        }

        $original = $this->config('model')::make([
            'context' => 'original',
            'disk' => $model->disk,
            'filename' => $model->filename,
            'directory' => $model->directory,
            'orig_filename' => $model->orig_filename,
            'extension' => $model->extension,
            'mime' => $model->mime,
            'size' => $model->size,
            'meta' => $model->meta
        ]);

        $model->filename = $this->filename($model);

        $this->storage()
            ->disk($model->disk)
            ->copy($original->relative_path, $model->relative_path);

        $model->save();

        $original->parent()->associate($model);
        $original->save();
    }

    public function relativePath(Model $model): string
    {
        return $model->filename ? $this->directory($model) . '/' . $model->filename : null;
    }

    public function resource($file)
    {
        try {
            if($file instanceof File) {
                return new FileResource($file);
            }
            else if($file instanceof Image) {
                return new ImageResource($file);
            }
            else if(is_string($file) && file_exists($file)) {
                return new FileResource(new File($file));
            }
            else if(is_string($file) && $stream = fopen($file, 'rb')) {
                return new RemoteResource($stream);
            }
            else if(is_resource($file)) {
                return new TmpResource($file);
            }
        }
        catch(\ErrorException $e) {
            //
        }

        throw new InvalidResourceException($file);
    }

    public function formatBytes($size) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($size, 0);
        $pow = min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision = 2) . ' ' . $units[$pow];
    }

    /**
     * Create and save an instance of Objectivehtml\Media\Model.
     * @param  array  $attributes
     * @param  Objectivehtml\Media\Contracts\StreamableResource $resource
     * @return Objectivehtml\Media\Model
     */
    public function save(array $attributes = [], StreamableResource $resource = null): Model
    {
        $model = $this->model($attributes, $resource);
        $model->save();
        
        return $model->fresh();
    }

    public function storage(): Factory
    {
        return $this->filesystem;
    }

    public function plugin(Plugin $plugin): PluginableInterface
    {
        $this->plugins->push($plugin);

        return $this;
    }

    public function isPluginInstalled($plugin)
    {
        if(!is_string($plugin)) {
            $plugin = get_class($plugin);
        }

        return $this->plugins()
            ->map(function($plugin) {
                return get_class($plugin);
            })
            ->contains($plugin);
    }

    public function plugins(): Collection
    {
        return $this->plugins->filter(function($plugin) {
            return $plugin->doesMeetRequirements();
        });
    }

    public function pluginsThatApplyTo(Model $model)
    {
        return $this->plugins()->filter(function($plugin) use ($model) {
            return $plugin->doesApply($model->mime, $model->extension);
        });
    }

    public function jobs(Model $model)
    {
        $globalJobs = ConfigClassStrategy::collect($this->config('jobs', []), $model);
        
        return collect()
            ->concat($globalJobs)
            ->concat($this->pluginsThatApplyTo($model)->map(function($plugin) use ($model) {
                return $plugin->jobs($model);
            }))
            ->flatten(1)
            ->filter();
    }

    public function filters(Model $model)
    {
        return $this->pluginsThatApplyTo($model)
            ->map(function($plugin) use ($model) {
                return $plugin->filters($model);
            })
            ->flatten(1)
            ->concat($model->filters);
    }

    public function conversions(Model $model)
    {
        return $this->pluginsThatApplyTo($model)
            ->map(function($plugin) use ($model) {
                return $plugin->conversions($model);
            })
            ->flatten(1)
            ->concat($model->conversions);
    }

}
