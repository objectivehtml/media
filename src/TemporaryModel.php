<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Contracts\StreamableResource;

class TemporaryModel extends Model
{
    protected $rewriteResource;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->disk = app(MediaService::class)->config('temp.disk', 'public');
        $this->context = app(MediaService::class)->config('temp.context', '__temp__');
    }

    /**
     * Set the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function setResource(?StreamableResource $resource)
    {
        if($this->exists) {
            $this->rewriteResource = true;
        }

        $this->resource = $resource;
    }

    public function shouldReplaceSource() {
        $resource = $this->resource();

        return $resource && (
            $this->parent->resource()->size() !== $resource->size()
        );
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function($model) {
            if($model->shouldReplaceSource()) {
                $model->parent->replaceResource($model->resource());
            }
        });
    }

}
