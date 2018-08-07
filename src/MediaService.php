<?php

namespace Objectivehtml\Media;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use Objectivehtml\Media\Model;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\VideoInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Objectivehtml\Media\Support\Configable;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Resources\FileResource;
use Objectivehtml\Media\Resources\ImageResource;
use FFMpeg\FFProbe\DataMapping\StreamCollection;
use Intervention\Image\ImageManagerStatic as Img;
use Objectivehtml\Media\Resources\RemoteResource;
use Objectivehtml\Media\Contracts\StreamableResource;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;
use Objectivehtml\Media\Contracts\Strategy as StrategyInterface;
use Objectivehtml\Media\Contracts\Configable as ConfigableInterface;

class MediaService implements ConfigableInterface {

    use Configable;

    protected $filesystem;

    protected $ffmpeg;

    protected $ffprobe;

    protected $plugins;

    public function __construct(Factory $filesystem, array $config)
    {
        $this->filesystem = $filesystem;
        $this->mergeConfig($config);

        $this->plugins = collect($this->config('plugins'))
            ->map(function($class) {
                return new $class();
            });
    }

    /**
     * Get the aspect ratio of video.
     *
     * @param  string $path
     * @return string
     */
    public function aspectRatio($width, $height): string
    {
        $gcd = function($width, $height) use (&$gcd) {
            return ($width % $height) ? $gcd($height, $width % $height) : $height;
        };

        $gcd = $gcd($width, $height);

        return $width/$gcd . ':' . $height/$gcd;
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
        collect([$attachTo])->each(function($attachTo) use ($model) {
            if(!$attachTo->media()->get()->contains($model)) {
                $attachTo->media()->attach($model);
            }
        });
    }

    /**
     * Get the bit rate of a video
     *
     * @param  {string} $path
     * @return {int}
     */
    public function bitRate($path)
    {
        return (int) $this->format($path)->get('bit_rate');
    }

    public function changeDisk(Model $model, $toDisk): Model
    {
        if($model->disk == $toDisk) {
            throw new Exceptions\CannotMoveModelException('Cannot move model to disk "'.$model->disk.'" because it already exists on that disk.');
        }

        $file = app(MediaService::class)->storage()->disk($model->disk)->get($model->relative_path);

        if(app(MediaService::class)->storage()->disk($toDisk)->put($model->relative_path, $file)) {
            app(MediaService::class)->storage()->disk($model->disk)->delete($model->relative_path);

            $model->disk = $toDisk;
            $model->save();
        }

        return $model;
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
        $key = app(MediaService::class)->keyName();

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

        return app(MediaService::class)->config('model', Model::class)::find($data[$key]);
    }

    /**
     * Get the dimensions of a video.
     *
     * @param  string $path
     * @return FFMpeg\Coordinate\Dimension
     */
    public function dimensions($path)
    {
        return $this->videos($path)->first()->getDimensions();
    }

    public function directory(Model $model, $strategy = null): string
    {
        if(!$strategy) {
            $strategy = $this->directoryStrategy($model);
        }

        return rtrim($strategy($model), '/');
    }

    public function directoryStrategy(): StrategyInterface
    {
        return $this->config('strategies.directory')::make();
    }

    /**
     * Get the duration of a video
     *
     * @param  {string} $path
     * @return {float}
     */
    public function duration($path)
    {
        return (float) $this->format($path)->get('duration');
    }

    public function extension(?string $path): ?string
    {
        return pathinfo($path, PATHINFO_EXTENSION) ?: null;
    }

    /**
     * Extract a single frame from a video file at a specified time (in seconds).
     *
     * @param  Objectivehtml\Media\Model  $model
     * @param  int  $timeInSeconds
     * @param  FFMpeg\Media\Video  $video
     * @return Objectivehtml\Media\Model
     */
    public function extractFrame(Model $model, $timeInSeconds = 0, Video $video = null): Model
    {
        $video = $video ?: $this->ffmpeg()->open($model->path);

        $child = app(MediaService::class)->model([
            'context' => 'frame',
            'disk' => $model->disk,
            'mime' => 'image/jpeg',
            'extension' => 'jpeg',
            'directory' => $model->directory,
        ]);

        $video->frame(TimeCode::fromSeconds($timeInSeconds))->save($child->path);

        $child->parent()->associate($model);
        $child->save();

        return $child;
    }

    /**
     * Get an instance of the FFMpeg library.
     *
     * @param  array  $config
     * @return FFMpeg\FFMpeg
     */
    public function ffmpeg(array $config = []): FFMpeg
    {
        if(!$this->ffmpeg) {
            $this->ffmpeg = FFMpeg::create(array_merge(app(MediaService::class)->config('ffmpeg'), $config));
        }

        return $this->ffmpeg;
    }

    /**
     * Create a FFProbe instance
     *
     * @return FFMpeg\FFProbe
     */
    public function ffprobe(array $config = []): FFProbe
    {
        if(!$this->ffprobe) {
            $this->ffprobe = FFProbe::create(array_merge(app(MediaService::class)->config('ffmpeg'), $config));
        }

        return $this->ffprobe;
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
     * Get the format of a video.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\Format
     */
    public function format($path)
    {
        return $this->ffprobe()->format($path);
    }

    /**
     * Get the models from the request.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection;
     */
    public function getModelsFromRequest(Request $request, $keys = null): Collection
    {
        return collect($keys ?: app(MediaService::class)->config('request'))
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
     * Get the height of a video
     *
     * @param  string $path
     * @return int
     */
    public function height($path): int
    {
        return (int) $this->dimensions($path)->getHeight();
    }

    /**
     * Create an instance of an Image object.
     *
     * @param  mixed $image
     * @return Intervention\Image\Image
     */
    public function image($image): Image
    {
        return Img::make($image);
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
            'disk' => $this->config('temp.disk'),
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

        if($matching = $this->matching($model)) {
            if($resource && ($attachTo = $resource->attachTo())) {
                app(MediaService::class)->attachTo($matching, $attachTo);
            }

            return $matching;
        }

        if($resource) {
            $model->resource($resource);
        }

        return $model;
    }

    public function path(...$parts): ?string
    {
        return $this->storage()->path(ltrim(implode($parts, '/'), '/'));
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
            throw new Exceptions\CannotPreserveOriginalException('Original already exists.');
        }

        $original = app(MediaService::class)->config('model')::make([
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

        $model->filename = app(MediaService::class)->filename($model);

        app(MediaService::class)
            ->storage()
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
        if($file instanceof File) {
            return new FileResource($file);
        }
        else if($file instanceof Image) {
            return new ImageResource($file);
        }
        else if(is_string($file) && file_exists($file)) {
            return new FileResource(new File($file));
        }

        throw new Exceptions\InvalidResourceException;
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

        return $model;
    }

    public function storage(): Factory
    {
        return $this->filesystem;
    }

    /**
     * Get the streams from a video file.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\StreamCollection
     */
    public function streams($path): StreamCollection
    {
        return $this->ffprobe()->streams($path);
    }

    public function url(...$parts): ?string
    {
        return $this->storage()->url(ltrim(implode($parts, '/'), '/'));
    }

    /**
     * Get the video streams from a path.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\StreamCollection
     */
    public function videos($path): StreamCollection
    {
        return $this->streams($path)->videos();
    }

    /**
     * Get the width of a video
     *
     * @param  string $path
     * @return int
     */
    public function width($path): int
    {
        return (int) $this->dimensions($path)->getWidth();
    }


    public function plugin(Plugin $plugin): PluginableInterface
    {
        $this->plugins->push($plugin);

        return $this;
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
        $globalJobs = collect(array_map(JobsConfigClassStrategy::make($model), $this->config('jobs', [])));

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
