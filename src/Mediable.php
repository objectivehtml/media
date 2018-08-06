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
            if(request()->has('files') && !request()->files->count()) {
                $model->media()->detach();
                //$model->save();
            }
            else {
                $model->addMediaFromRequest(request());
            }
        });

        static::deleting(function ($model) {
            $model->media()->detach();
        });
    }

    /**
     * Add a media resource to this model instance.
     *
     * @param $resource
     * @return Illuminate\Eloquent\Database\Model
     */
    public function addMedia($resource, Closure $callback = null): Model
    {
        $resource = $this->resource($resource);

        if($callback) {
            $callback($resource);
        }

        return $resource->attachTo($this)->save();
    }

    /**
     * Add a media resource from a Request.
     *
     * @param $resource
     * @return Illuminate\Eloquent\Database\Model
     */
    public function addMediaFromRequest($request, Closure $callback = null): Collection
    {
        if($request instanceof Request) {
            $data = $request->file();
        }
        else if ($request instanceof FileBag || is_iterable($request)) {
            $data = $request;
        }
        else {
            throw new InvalidResourceException;
        }

        $return = collect();

        foreach($data as $files) {
            foreach((!is_array($files) ? [$files] : $files) as $file) {
                $return->push($this->addMedia($file, $callback));
            }
        }

        return $return;
    }

    /**
     * Get all of the model's media.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(app(MediaService::class)->config('model'), 'mediable');
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

        $query = app(MediaService::class)->config('model')::query();

        return new MorphOneThrough($query, $this, $name, $table,
                                   $foreignPivotKey, $relatedPivotKey, $parentKey,
                                   $relatedKey, $relationName = null, $inverse = false);
    }

    /**
     * Get a media resource for the associated file.
     *
     * @param $resource
     * @return Objectivehtml\Media\Contracts\StreamableResource
     */
    public function resource($resource): StreamableResourceInterface
    {
        return app(MediaService::class)->resource($resource);
    }

}
