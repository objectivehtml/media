<?php

namespace Objectivehtml\Media;

use Closure;
use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Objectivehtml\Media\Exceptions\InvalidResourceException;
use Objectivehtml\Media\Contracts\StreamableResource as StreamableResourceInterface;

trait Mediable {

    protected static function bootMediable()
    {
        static::saved(function ($model) {
            if($model->shouldUseObserver()) {
                if($model->shouldUseSync()) {
                    $model->syncMediaFromRequest(request());
                }
                else {
                    $model->addMediaFromRequest(request());
                }
            }
        });

        static::deleting(function ($model) {
            $model->media()->detach();
        });
    }

    /**
     * Method to override the default config value that determines if this
     * model should use the observer.
     *
     * @return bool
     */
    public function shouldUseObserver(): bool
    {
        return app(MediaService::class)->config('use_observer', true);
    }

    /**
     * Method to override the default config value that determines if this
     * model should use the observer.
     *
     * @return bool
     */
    public function shouldUseSync(): bool
    {
        return app(MediaService::class)->config('use_sync', true);
    }

    /**
     * Add a media resource to this model instance.
     *
     * @param  $resource
     * @return Illuminate\Eloquent\Database\Model
     */
    public function addMedia($resource, Closure $callback = null): Model
    {
        $resource = $this->resource($resource);

        if($callback) {
            $callback($resource);
        }

        $resource->attachTo($this);

        return $resource->save();
    }

    /**
     * Add media resources from a Request.
     *
     * @param  Illuminate\Http\Request $resource
     * @param  Closure $callback
     * @param  bool $sync
     * @return Illuminate\Eloquent\Database\Model
     */
    public function addMediaFromRequest(Request $request, Closure $callback = null, bool $detach = false): Collection
    {
        $files = collect($request->file())
            ->only(app(MediaService::class)->config('request'))
            ->flatten(1);

        $input = app(MediaService::class)
            ->getModelsFromRequest($request);

        if($detach && $files->count() + $input->count()) {
            $this->media()->detach();
        }

        $fileModels = collect($files)->map(function($file) use ($callback) {
            return $this->addMedia($file, $callback);
        });

        $inputModels = $input->each(function($model) {
            app(MediaService::class)->attachTo($model, $this);
        });

        return $fileModels->concat($inputModels);
    }

    /**
     * Sync media resource from a Request.
     *
     * @param  Illuminate\Http\Request $resource
     * @param  Closure $callback
     * @return Illuminate\Eloquent\Database\Model
     */
    public function syncMediaFromRequest(Request $request, Closure $callback = null): Collection
    {
        return $this->addMediaFromRequest($request, $callback, true);
    }

    /**
     * Get all of the model's media.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(app(MediaService::class)->config('model', Model::class), 'mediable', null, 'mediable_id', 'model_id');
    }

    /**
     * Get the first associated media model.
     *
     * @return Objectivehtml\Media\MorphOneThrough
     */
    public function medium()
    {
        return $this->morphOneThrough('mediable', 'mediables', 'mediable_id', 'model_id', 'id', 'id');
    }

    /**
     * Helper method to get the media tags as images.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function images(): MorphToMany
    {
        return $this->media()->images();
    }

    /**
     * Helper method to get the media tags as images.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function videos(): MorphToMany
    {
        return $this->media()->videos();
    }

    /**
     * Helper method to get the media tags as images.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function audio(): MorphToMany
    {
        return $this->media()->audio();
    }

    /**
     * Custom/hack helper function. This allows eager loading of relationships,
     * but instead of returning a collection, it returns the first result found.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string  $relationName
     * @return void
     */
    public function morphOneThrough($name, $table, $foreignPivotKey,
                                    $relatedPivotKey, $parentKey, $relatedKey,
                                    $relationName = null, $inverse = false)
    {

        $query = app(MediaService::class)->config('model', Model::class)::query();

        return new MorphOneThrough($query, $this, $name, $table,
                                   $foreignPivotKey, $relatedPivotKey, $parentKey,
                                   $relatedKey, $relationName = null, $inverse = false);
    }

    /**
     * Get a media resource for the associated file.
     *
     * @param  mixed $resource
     * @return Objectivehtml\Media\Contracts\StreamableResource
     */
    public function resource($resource): StreamableResourceInterface
    {
        return app(MediaService::class)->resource($resource);
    }

}
