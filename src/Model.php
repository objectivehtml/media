<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Filters\Filters;
use Objectivehtml\Media\Support\Metable;
use Objectivehtml\Media\Jobs\StartQueue;
use Objectivehtml\Media\Jobs\MarkAsReady;
use Objectivehtml\Media\Jobs\ApplyFilter;
use Objectivehtml\Media\Jobs\ApplyFilters;
use Objectivehtml\Media\Jobs\GenerateImages;
use Objectivehtml\Media\Jobs\ApplyConversion;
use Objectivehtml\Media\Jobs\MoveModelToDisk;
use Objectivehtml\Media\Jobs\ApplyConversions;
use Objectivehtml\Media\Conversions\Conversions;
use Objectivehtml\Media\Jobs\RemoveModelFromDisk;
use Objectivehtml\Media\Jobs\StartProcessingMedia;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Objectivehtml\Media\Contracts\StreamableResource;

class Model extends BaseModel
{
    /**
     * The resource associated to the model.
     *
     * @var resource
     */
    protected $resource;

    /**
     * The database table name.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ready',
        'disk',
        'context',
        'title',
        'caption',
        'directory',
        'filename',
        'orig_filename',
        'mime',
        'extension',
        'size',
        'filters',
        'conversions',
        'meta',
        'tags',
        'order'
    ];

    /**
     * The attributes that are hidden.
     *
     * @var array
     */
    protected $hidden = [
        'resource',
    ];

    /**
     * The attributes that are cast.
     *
     * @var array
     */
    protected $casts = [
        'ready' => 'bool',
        'filters' => 'array',
        'conversions' => 'array',
        'tags' => 'collection',
        'meta' => 'collection',
    ];

    /**
     * The attributes that are appended.
     *
     * @var array
     */
    protected $appends = [
        'relative_path',
        'url'
    ];

    /**
     * The default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'size' => 0
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if(!$this->disk) {
            $this->disk = app(MediaService::class)->config('disk');
        }

        if(!$this->extension) {
            $this->extension = app(MediaService::class)->extension($this->filename ?: $this->orig_filename);
        }

        if(!$this->filename) {
            $this->filename = app(MediaService::class)->filename($this);
        }
    }

    /**
     * Get all of the owning mediable models.
     */
    public function mediable(string $class)
    {
        return $this->morphedByMany($class, 'mediable', 'mediables', 'id', 'model_id');
    }

    /**
     * Get the user model.
     *
     * @return [type] [description]
     */
    public function user()
    {
        // TODO: auth()->guard()->getProvider()->getModel()
        return $this->belongsTo(static::class, 'user_id');
    }

    /**
     * Get the parent model.
     *
     * @return [type] [description]
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * Get the children models.
     *
     * @return [type] [description]
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Apply the filters to the model's file.
     *
     * @return void
     */
    public function applyConversions()
    {
        $conversions = app(MediaService::class)->conversions($this)->map(function($conversion) {
            return new ApplyConversion($this, $conversion);
        });

        StartQueue::withChain($conversions)->dispatch();
    }

    /**
     * Apply the filters to the model's file.
     *
     * @return void
     */
    public function applyFilters()
    {
        $filters = app(MediaService::class)->filters($this)->map(function($filter) {
            return new ApplyFilter($this, $filter);
        });

        StartQueue::withChain($filters)->dispatch();
    }

    /**
     * Get the file_exists attribute.
     *
     * @param $value
     */
    public function getFileExistsAttribute()
    {
        return $this->doesFileExist();
    }

    /**
     * Get height of an image or video file.
     *
     * @param $value
     */
    public function getHeightAttribute()
    {
        return $this->meta->get('height');
    }

    /**
     * Get the meta attribute.
     *
     * @param $value
     */
    public function getMetaAttribute($value)
    {
        return $this->castAttribute('meta', $value) ?: collect();
    }

    /**
     * Get the path for the associated file.
     *
     * @param $value
     */
    public function getPathAttribute()
    {
        return $this->directory ? app(MediaService::class)->storage()->disk($this->disk)->path($this->relative_path) : null;
    }

    /**
     * Get the relative path for the associated file.
     *
     * @param $value
     */
    public function getRelativePathAttribute()
    {
        return $this->filename ? ($this->directory ? $this->directory . '/' : null) . $this->filename : null;
    }

    /**
     * Get the tags attribute.
     *
     * @param $value
     */
    public function getTagsAttribute($value)
    {
        return $this->castAttribute('tags', $value) ?: collect();
    }

    /**
     * Get width of an image or video file.
     *
     * @param $value
     */
    public function getWidthAttribute()
    {
        return $this->meta->get('width');
    }

    /**
     * Get the path for the associated file.
     *
     * @param $value
     */
    public function getUrlAttribute()
    {
        return $this->filename ? app(MediaService::class)->url($this->directory, $this->filename) : null;
    }

    /**
     * Get the conversions as a collection.
     *
     * @param $value
     */
    public function getConversionsAttribute($value)
    {
        return new Conversions($this->castAttribute('conversions', $value) ?: []);
    }

    /**
     * Get the filter as a collection.
     *
     * @param $value
     */
    public function getFiltersAttribute($value)
    {
        return new Filters($this->castAttribute('filters', $value));
    }

    /**
     * Is this model a parent.
     *
     * @return boolean
     */
    public function isParent(): bool
    {
        return $this->parent()->count() === 0;
    }

    /**
     * Does the file exist on the model's disk.
     *
     * @return boolean
     */
    public function doesFileExist(): bool
    {
        return app(MediaService::class)->storage()->disk($this->disk)->exists($this->relative_path);
    }

    /**
     * Set the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function resource(StreamableResource $resource = null)
    {
        if($resource) {
            $this->resource = $resource;

            return $this;
        }

        return $this->resource;
    }

    /**
     * Get the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function getResource(): StreamableResource
    {
        return $this->resource;
    }

    /**
     * Add a query scope for the caption attribute
     *
     * @param $value
     */
    public function scopeCaption($query, $value)
    {
        $query->whereCaption($value);
    }

    /**
     * Add a query scope for the context attribute
     *
     * @param $value
     */
    public function scopeContext($query, $value)
    {
        $query->whereContext($value);
    }

    /**
     * Add a query scope for the conversions attribute
     *
     * @param $value
     */
    public function scopeConversion($query, $conversion)
    {
        $query->whereRaw('JSON_CONTAINS(`conversions`, '.json_encode($conversion).')');
    }

    /**
     * Add a query scope for the disk attribute
     *
     * @param $value
     */
    public function scopeDisk($query, $value)
    {
        $query->whereDisk($value);
    }

    /**
     * Add a query scope for the extension attribute
     *
     * @param $value
     */
    public function scopeExtension($query, $value)
    {
        $query->whereExtension($value);
    }

    /**
     * Add a query scope for the filename attribute
     *
     * @param $value
     */
    public function scopeFilename($query, $value)
    {
        $query->whereFilename($value);
    }

    /**
     * Add a query scope for the filters attribute
     *
     * @param $value
     */
    public function scopeFilter($query, $filter)
    {
        $query->whereRaw('JSON_CONTAINS(`filters`, '.json_encode($filter).')');
    }

    /**
     * Add a query scope for the meta attribute
     *
     * @param $value
     */
    public function scopeMeta($query, $meta)
    {
        $query->whereRaw('JSON_CONTAINS(`meta`, '.json_encode($meta).')');
    }

    /**
     * Add a query scope for the mime attribute
     *
     * @param $value
     */
    public function scopeMime($query, $value)
    {
        $query->whereMime($value);
    }

    /**
     * Add a query scope for the original context
     *
     * @param $value
     */
    public function scopeOriginal($query)
    {
        $query->context('original');
    }

    /**
     * Add a query scope for the orig_filename attribute
     *
     * @param $value
     */
    public function scopeOrigFilename($query, $value)
    {
        $query->whereOrigFilename($value);
    }

    /**
     * Add a query scope for the orig_filename attribute
     *
     * @param $value
     */
    public function scopeParents($query)
    {
        $query->whereNull('parent_id');
    }

    /**
     * Add a query scope for the size attribute
     *
     * @param $value
     */
    public function scopeSize($query, $value)
    {
        $query->whereSize($value);
    }

    /**
     * Add a query scope for the title attribute
     *
     * @param $value
     */
    public function scopeTitle($query, $value)
    {
        $query->whereTitle($value);
    }

    /**
     * Ensure all directory values set do not '/' at the end
     *
     * @param $value
     */
    public function setDirectoryAttribute($value)
    {
        $this->attributes['directory'] = $value ? rtrim($value, '/') : null;
    }

    /**
     * Ensure all directory values set do not '/' at the end
     *
     * @param $value
     */
    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = json_encode($value);
    }

    /**
     * Set the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function setResource(?StreamableResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get and set meta key/value pairs
     *
     * @param $key
     * @param $value
     */
    public function meta($key = null, $value = null)
    {
        if(is_array($key)) {
            foreach($key as $index => $value) {
                $this->meta($index, $value);
            }
        }
        else {
            $meta = $this->meta;
            $meta->put($key, $value);
            $this->setAttribute('meta', $meta->filter());
        }
    }

    /**
     * Get and set meta key/value pairs
     *
     * @param $key
     * @param $value
     */
    public function tag(...$tags): self
    {
        foreach($tags as $tag) {
            if(!$this->tags->contains($tag)) {
                $this->tags = $this->tags->push($tag);
            }
        }

        return $this;
    }

    /**
     * Get and set meta key/value pairs
     *
     * @param $key
     * @param $value
     */
    public function tags(array $tags): self
    {
        foreach($tags as $tag) {
            $this->tag($tag);
        }

        return $this;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(MediaObserver::class);

        foreach(app(MediaService::class)->plugins() as $plugin) {
            static::observe($plugin);
        }

        static::saving(function(Model $model) {
            //$model->filters = app(MediaService::class)->filters($model);
            //$model->conversions = app(MediaService::class)->conversions($model);

            if($model->mime) {
                $model->tag(explode('/', $model->mime)[0]);
            }
        });

        static::saved(function(Model $model) {
            if($model->resource() && ($attachTo = $model->resource()->attachTo())) {
                app(MediaService::class)->attachTo($model, $attachTo);
            }
        });

        static::created(function(Model $model) {
            $toDisk = app(MediaService::class)->config('disk');

            if(($resource = $model->resource()) && !$model->fileExists) {
                $toDisk = $resource->disk() ?: $toDisk;
            }

            if($model->isParent() && $model->fileExists) {
                StartProcessingMedia::withChain(
                    app(MediaService::class)->jobs($model)
                        ->concat([
                            new ApplyConversions($model),
                            new ApplyFilters($model),
                            new MoveModelToDisk($model, $toDisk),
                            new MarkAsReady($model)
                        ])
                )->dispatch($model);
            }
            else if($model->fileExists) {
                StartProcessingMedia::withChain([
                    new ApplyFilters($model),
                    new MoveModelToDisk($model, $toDisk),
                    new MarkAsReady($model),
                ])->dispatch($model);
            }
        });
    }

}
