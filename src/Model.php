<?php

namespace Objectivehtml\Media;

use Objectivehtml\Media\Jobs\MarkAsReady;
use Objectivehtml\Media\Jobs\ApplyFilter;
use Objectivehtml\Media\Support\ExifData;
use Objectivehtml\Media\Support\QueryScopes;
use Illuminate\Filesystem\FilesystemManager;
use Objectivehtml\Media\Jobs\ApplyConversion;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Events\FavoritedMedia;
use Objectivehtml\Media\Events\MovedModelToDisk;
use Objectivehtml\Media\Events\UnfavoritedMedia;
use Objectivehtml\Media\Jobs\StartProcessingMedia;
use Objectivehtml\Media\Jobs\FinishProcessingMedia;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Objectivehtml\Media\Events\ReplacedModelResource;
use Objectivehtml\Media\Contracts\StreamableResource;
use Objectivehtml\Media\Exceptions\InvalidResourceException;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class Model extends BaseModel
{
    use QueryScopes;

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
        'ready', 'favorite', 'disk', 'context', 'title', 'caption', 'directory',
        'filename', 'orig_filename', 'mime', 'extension', 'size','disk',
        'filters', 'conversions', 'meta', 'tags', 'order', 'taken_at'
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
        'meta' => 'collection'
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
     * The date attributes.
     *
     * @var array
     */
    protected $dates = [
        'taken_at'
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
            $this->disk = app(MediaService::class)->config('temp.disk');
        }

        if(!$this->extension) {
            $this->extension = app(MediaService::class)->extension($this->filename ?: $this->orig_filename);
        }

        if(!$this->filename) {
            $this->filename = app(MediaService::class)->filename($this);
        }

        if(!$this->taken_at) {
            $this->taken_at = now();
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
     * Change the filename's extension in the database. This method does not
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
     * Get the exif attribute.
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

    /**
     * Set the exif attribute.
     *
     * @param $value
     */
    public function setExifAttribute(?array $value)
    {
        $this->meta('exif', $value);
    }

    /**
     * Get the filesize attribute.
     *
     * @return float
     */
    public function getFilesizeAttribute()
    {
        return app(MediaService::class)->formatBytes($this->size);
    }

    /**
     * Get the file_exists attribute.
     *
     * @return bool
     */
    public function getFileExistsAttribute(): bool
    {
        return $this->doesFileExist();
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
     * Get height of an image or video file.
     *
     * @param $value
     */
    public function getHeightAttribute()
    {
        return $this->meta->get('height');
    }

    /**
     * Get height of an image or video file.
     *
     * @param $value
     */
    public function setHeightAttribute($value)
    {
        return $this->meta('height', $value);
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
        return !is_null($this->directory) ? $this->storage()->disk($this->disk)->path($this->relative_path) : null;
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
     * Get the path for the associated file.
     *
     * @param $value
     */
    public function getUrlAttribute()
    {
        return !is_null($this->directory) ? $this->storage()->disk($this->disk)->url($this->relative_path) : null;
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
     * Get width of an image or video file.
     *
     * @param $value
     */
    public function setWidthAttribute($value)
    {
        return $this->meta('width', $value);
    }

    /**
     * Does the file exist on the model's disk.
     *
     * @return boolean
     */
    public function doesFileExist(): bool
    {
        return $this->storage()->disk($this->disk)->exists($this->relative_path);
    }

    public function replaceResource(StreamableResource $resource)
    {
        $response = $this->storage()->disk($this->disk)->put(
            $this->relative_path, $resource->stream()
        );

        if($response) {
            $this->resource = $resource;
    
            event(new ReplacedModelResource($this));
        }

        return $response;
    }

    public function ensureTemporaryDirectoryExists()
    {
        $path = $this->storage()
            ->disk(app(MediaService::class)->config('temp.disk'))
            ->path($this->relative_path);

        if(!file_exists($directory = dirname($path))) {
            mkdir($directory);
        }
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
     * Get the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function getResource(): ?StreamableResource
    {
        if($this->resource) {
            return $this->resource;
        }
        else if(file_exists($path = $this->path ?: $this->url)) {
            return $this->resource = app(MediaService::class)->resource($path);
        }

        return null;
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
     * Set the resource property.
     *
     * @param  StreamableResource $resource
     * @return mixed
     */
    public function resource(StreamableResource $resource = null)
    {
        if($resource) {
            $this->setResource($resource);
            
            return $this;
        }

        if($this->resource) {
            return $this->resource;
        }

        if(file_exists($this->path)) {
            return app(MediaService::class)->resource($this->path);
        }

        try {
            return app(MediaService::class)->resource($this->url);
        }
        catch(InvalidResourceException $e) {
            return null;
        }
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
     * Mark this model as a favorite in the database.
     *
     * @return Objectivehtml\Media\Model
     */
    public function favorite()
    {
        $this->favorite = true;
        $this->save();

        event(new FavoritedMedia($this));

        return $this;
    }

    /**
     * Remove the favorite status from this model in the database.
     *
     * @return Objectivehtml\Media\Model
     */
    public function unfavorite()
    {
        $this->favorite = false;
        $this->save();

        event(new UnfavoritedMedia($this));

        return $this;
    }

    /**
     * Helper to get the file system from the MediaService provider.
     *
     * @return Objectivehtml\Media\Model
     */
    public function storage(): FilesystemManager
    {
        return app(MediaService::class)->storage();
    }

    /**
     * Determines if the model needs moved to another disk.
     *
     * @return bool
     */
    public function shouldChangeDisk(): bool
    {
        if($this->isParent()) {
            return $this->ready && $this->meta->get('move_to');
        }

        return $this->ready && !$this->parent->temporary();
    }

    public function isGeocoded(): bool
    {
        return !!$this->meta->get('geocoder');
    }

    public function shouldGeocodeExif(): bool
    {
        return !$this->isGeocoded() && (
            $this->exif && $this->exif->latitude && $this->exif->longitude
        );
    }

    /**
     * Move the move to another disk and queues the old file for deletion.
     *
     * @return void
     */
    public function moveToDisk($disk)
    {
        // Check to see if the model's current disk matches the disk that the
        // model is being changed to. If a match, just ignore the request.
        if($this->disk !== $disk) {
            $response = $this->storage()->disk($disk)->writeStream(
                $this->relative_path, $this->storage()->disk($this->disk)->readStream($this->relative_path)
            );

            if($response) {
                $event = new MovedModelToDisk($this, $this->disk);
                
                $this->disk = $disk;
                $this->meta('move_to', null);
                $this->save();

                event($event);
            }
        }
        // else {
            // Use to throw an error... testing to see if silence is better.
            // throw new Exceptions\CannotMoveModelException('Cannot move model to disk "'.$this->disk.'" because it already exists on that disk.');
        // }

        return $this;
    }

    /**
     * Determines if the model is being stored on the temp disk. This method
     * intentionally ignores models with the __temp__ context because those
     * models are always stored on the temp disk and get deleted automatically.
     *
     * @return bool
     */
    public function temporary(): bool
    {
        return !$this->isTemporaryFile() &&
                $this->disk === app(MediaService::class)->config('temp.disk', 'public') &&
                $this->disk !== app(MediaService::class)->config('disk', 'public');
    }

    /**
     * Determines if the model represents a temporary (local only) file that
     * is automatically deleted after processing.
     *
     * @return bool
     */
    public function isTemporaryFile(): bool
    {
        return $this->context === app(MediaService::class)->config('temp.context', '__temp__');
    }

    /**
     * Perform the various encoding
     *
     * @return void
     */
    public function encode()
    {
        $jobs = collect();

        if($this->isParent() && $this->fileExists) {
            $jobs = $jobs
                ->concat(app(MediaService::class)->jobs($this))
                ->concat(
                    app(MediaService::class)
                        ->conversions($this)
                        ->map(function($conversion) {
                            return new ApplyConversion($this, $conversion);
                        })
                )
                ->concat(
                    app(MediaService::class)
                        ->filters($this)
                        ->map(function($filter) {
                            return new ApplyFilter($this, $filter);
                        })
                );
        }
        else if(file_exists($this->path)) {
            $jobs = $jobs
                ->concat(
                    $this->conversions->map(function($conversion) {
                        return new ApplyConversion($this, $conversion);
                    })
                )
                ->concat(
                    $this->filters->map(function($filter) {
                        return new ApplyFilter($this, $filter);
                    })
                );
        }

        $jobs = $jobs->concat([
            new MarkAsReady($this),
            new FinishProcessingMedia($this)
        ]);
        
        StartProcessingMedia::withChain($jobs->all())->dispatch($this);
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

        app(MediaService::class)
            ->plugins()
            ->each(function($plugin) {
                $plugin->observe(static::class);
            });
        
        static::created(function(Model $model) {
            if(!$model->isTemporaryFile()) {
                $model->encode();
            }
        });
    }

}
