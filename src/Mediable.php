<?php

namespace Objectivehtml\Media;

use Closure;
use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Objectivehtml\Media\Exceptions\InvalidResourceException;
use Objectivehtml\Media\Contracts\StreamableResource as StreamableResourceInterface;

trait Mediable {

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
     * Get all of the post's comments.
     */
    public function media()
    {
        return $this->morphToMany(app(MediaService::class)->config('model'), 'mediable');
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
