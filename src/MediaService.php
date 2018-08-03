<?php

namespace Objectivehtml\MediaManager;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use FFMpeg\Format\VideoInterface;
use FFMpeg\Coordinate\TimeCode;
use Intervention\Image\Image;
use Objectivehtml\MediaManager\Model;
use Illuminate\Contracts\Filesystem\Factory;
use Symfony\Component\HttpFoundation\File\File;
use FFMpeg\FFProbe\DataMapping\StreamCollection;
use Intervention\Image\ImageManagerStatic as Img;
use Objectivehtml\MediaManager\Support\Configable;
use Objectivehtml\MediaManager\Support\Pluginable;
use Objectivehtml\MediaManager\Resources\FileResource;
use Objectivehtml\MediaManager\Resources\ImageResource;
use Objectivehtml\MediaManager\Contracts\StreamableResource;
use Objectivehtml\MediaManager\Contracts\Strategy as StrategyInterface;
use Objectivehtml\MediaManager\Contracts\Configable as ConfigableInterface;

class MediaService implements ConfigableInterface {

    use Configable, Pluginable;

    protected $filesystem;

    protected $ffmpeg;

    protected $ffprobe;

    public function __construct(Factory $filesystem, array $config)
    {
        $this->filesystem = $filesystem;
        $this->mergeConfig($config);
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

    public function create(array $attributes = []): Model
    {
        return $this->config('model')::create($attributes);
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
            $strategy = $this->directoryStrategy();
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
     * @param  Objectivehtml\MediaManager\Model  $model
     * @param  int  $timeInSeconds
     * @param  FFMpeg\Media\Video  $video
     * @return Objectivehtml\MediaManager\Model
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
     * Get the height of a video
     *
     * @param  string $path
     * @return int
     */
    public function height($path): int
    {
        return (int) $this->dimensions($path)->getHeight();
    }

    public function image($image): Image
    {
        return Img::make($image);
    }

    public function model(array $attributes = [], StreamableResource $resource = null): Model
    {
        return $this->config('model')::make(array_merge([
            'disk' => $this->config('temp.disk'),
            'context' => $resource ? $resource->context() : null,
            'directory' => $resource ? $resource->directory() : null,
            'orig_filename' => $resource ? $resource->originalFilename() : null,
            'mime' => $resource ? $resource->mime() : null,
            'size' => $resource ? $resource->size() : null,
            'filters' => $resource ? $resource->filters() : null,
            'conversions' => $resource ? $resource->conversions() : null,
            'meta' => $resource ? $resource->meta() : null,
            'tags' => $resource ? $resource->tags() : null
        ], $attributes));
    }

    public function path(...$parts): ?string
    {
        return $this->storage()->path(ltrim(implode($parts, '/'), '/'));
    }

    /**
     * Copy the file and preserve it as the original.
     *
     * @param  Model $model
     * @return Model
     */
    public function preserveOriginal(Model $model): Model
    {
        if($model->children()->context('original')->count()) {
            throw new Exceptions\CannotPreserveOriginalException('Original already exists.');
        }

        $original = app(MediaService::class)->model([
            'context' => 'original',
            'disk' => $model->disk,
            'directory' => $model->directory,
            'orig_filename' => $model->orig_filename,
            'extension' => $model->extension,
            'mime' => $model->mime,
            'size' => $model->size,
            'meta' => $model->meta
        ]);

        if(app(MediaService::class)->storage()->disk($model->disk)->copy($model->relative_path, $original->relative_path)) {
            $original->parent()->associate($model);
            $original->save();
        }

        return $original;
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

        throw new Exceptions\InvalidResourceException;
    }

    public function save(StreamableResource $resource, array $attributes = []): Model
    {
        $model = $this->model($attributes, $resource);
        $model->resource($resource);
        $model->save();

        // 1. Create model for original file if set to preserveOriginal.
        // 2. Take parent model and apply global filters, like max size.
        // 3. Apply model specific Filters
        // 4. Save generated image file, strip exif, and optimize image.
        // 5. Check for applicable conversions and convert the file
        // 6. Check for applicable image generators and extract the images. Extracted images re-run this routine.
        // 7. Once filters have been applied, conversions created, generated ran, then file can be moved to final destination (from temp directory).
        // 8. After being moved to final disk destination, save model as `ready`.

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

}
