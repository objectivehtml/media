<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Support\Metable;
use Objectivehtml\Media\Jobs\StartQueue;
use Objectivehtml\Media\Jobs\MarkAsReady;
use Objectivehtml\Media\Jobs\ApplyFilter;
use Objectivehtml\Media\Jobs\ApplyFilters;
use Objectivehtml\Media\Support\ExifData;
use Objectivehtml\Media\Support\QueryScopes;
use Objectivehtml\Media\Jobs\GenerateImages;
use Objectivehtml\Media\Events\FavoriteMedia;
use Objectivehtml\Media\Jobs\ApplyConversion;
use Objectivehtml\Media\Jobs\MoveModelToDisk;
use Objectivehtml\Media\Jobs\ApplyConversions;
use Objectivehtml\Media\Events\UnfavoriteMedia;
use Objectivehtml\Media\Conversions\Conversions;
use Objectivehtml\Media\Jobs\RemoveModelFromDisk;
use Objectivehtml\Media\Jobs\StartedProcessingMedia;
use Objectivehtml\Media\Jobs\StoppedProcessingMedia;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Objectivehtml\Media\Contracts\StreamableResource;
use Intervention\Image\Exception\NotReadableException;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class Model extends BaseModel
{
    use QueryScopes;

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
        'favorite',
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
        'parent_id', 'resource',
    ];

    /**
     * The attributes that are cast.
     *
     * @var array
     */
    protected $casts = [
        'ready' => 'bool',
        'favorite' => 'bool',
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
        'filesize',
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
        return $this->morphedByMany($class, 'mediable');
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
    public function applyConversions(bool $useGlobalConversions = true)
    {
        if($useGlobalConversions) {
            $conversions = app(MediaService::class)->conversions($this);
        }
        else {
            $conversions = $this->conversions;
        }

        $conversions = collect($conversions)->map(function($conversion) {
            return new ApplyConversion($this, $conversion);
        });

        StartQueue::withChain($conversions->toArray())->dispatch();
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

        StartQueue::withChain($filters->toArray())->dispatch();
    }

    /**
     * Set the extension attribute and automatically change the extension on
     * the filename.
     *
     * @param string $value
     * @return void
     */
    public function setExtensionAttribute($value)
    {
        if($value && $this->extension) {
            $this->changeFilenameExtension($value);
        }

        $this->attributes['extension'] = $value;
    }


    /**
     * Change the filebane's extension in the database. This method does not
     * actually alter the file.
     *
     * @param  string $extension
     * @return void
     */
    public function changeFilenameExtension(string $extension)
    {
        if($this->filename && $extension) {
            $this->filename = str_replace('.'.$this->extension, '.'.$extension, $this->filename);
        }
    }

    /**
     * Get the file_exists attribute.
     *
     * @param $value
     */
    public function getExifAttribute(): ?ExifData
    {
        if($exif = $this->meta->get('exif')) {
            return new ExifData($exif);
        }

        return null;
    }

    public function getFilesizeAttribute()
    {
        return app(MediaService::class)->formatBytes($this->size);
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
        return collect($this->castAttribute('conversions', $value))
            ->map(function($conversion) {
                if($conversion instanceof ConversionInterface) {
                    return $conversion;
                }
                else if(is_array($conversion)) {
                    if(isset($conversion[1])) {
                        return $conversion[0]::make(...$conversion[1]);
                    }

                    return $conversion[0]::make();
                }
            });
    }

    /**
     * Get the filter as a collection.
     *
     * @param $value
     */
    public function getFiltersAttribute($value)
    {
        return collect($this->castAttribute('filters', $value))
            ->map(function($filter) {
                if($filter instanceof FilterInterface) {
                    return $filter;
                }
                else if(is_array($filter)) {
                    if(isset($filter[1])) {
                        return $filter[0]::make(...$filter[1]);
                    }

                    return $filter[0]::make();
                }
            });
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
            $this->setAttribute('meta', $meta->filter(function($item) {
                return !is_null($item);
            }));
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

    public function favorite()
    {
        $this->favorite = true;
        $this->save();

        event(new FavoriteMedia($this));

        return $this;
    }

    public function unfavorite()
    {
        $this->favorite = false;
        $this->save();

        event(new UnfavoriteMedia($this));

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
                $jobs = collect()
                    ->concat(app(MediaService::class)->jobs($model))
                    ->concat(
                        app(MediaService::class)
                            ->conversions($model)
                            ->map(function($conversion) use ($model) {
                                return new ApplyConversion($model, $conversion);
                            })
                    )
                    ->concat(
                        app(MediaService::class)
                            ->filters($model)
                            ->map(function($filter) use ($model) {
                                return new ApplyFilter($model, $filter);
                            })
                    )
                    ->concat([
                        new MoveModelToDisk($model, $toDisk),
                        new MarkAsReady($model),
                        new StoppedProcessingMedia($model)
                    ]);

                StartedProcessingMedia::withChain($jobs)->dispatch($model);
            }
            else if(file_exists($model->path)) {
                $jobs = collect()
                    ->concat(
                        $model->filters->map(function($filter) use ($model) {
                            return new ApplyFilter($model, $filter);
                        })
                    )
                    ->concat(
                        $model->conversions->map(function($conversion) use ($model) {
                            return new ApplyConversion($model, $conversion);
                        })
                    )
                    ->concat([
                        new MoveModelToDisk($model, $toDisk),
                        new MarkAsReady($model),
                        new StoppedProcessingMedia($model)
                    ]);

                StartedProcessingMedia::withChain($jobs)->dispatch($model);
            }
        });
    }

}
