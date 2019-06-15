<?php

namespace Objectivehtml\Media\Services;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use Objectivehtml\Media\Model;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Collection;
use FFMpeg\FFProbe\DataMapping\StreamCollection;

class VideoService extends Service {

    protected $ffmpeg;

    protected $ffprobe;

    /**
     * Get the aspect ratio of video.
     *
     * @param  string $path
     * @return string
     */
    public function aspectRatio(float $width, float $height): string
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
    public function bitRate(string $path)
    {
        return (int) $this->format($path)->get('bit_rate');
    }

    /**
     * Get the dimensions of a video.
     *
     * @param  string $path
     * @return FFMpeg\Coordinate\Dimension
     */
    public function dimensions(string $path)
    {
        return $this->videos($path)->first()->getDimensions();
    }

    /**
     * Get the duration of a video
     *
     * @param  {string} $path
     * @return {float}
     */
    public function duration(string $path)
    {
        return (float) $this->format($path)->get('duration');
    }

    /**
     * Extract a single frame from a video file at a specified time (in seconds).
     *
     * @param  mixed  $subject
     * @param  int  $timeInSeconds
     * @param  FFMpeg\Media\Video  $video
     * @return Objectivehtml\Media\Model
     */
    public function extractFrame($subject, $timeInSeconds = 0, Video $video = null): Model
    {
        $parent = null;
        $directory = null;

        if($subject instanceof Model) {
            $parent = $subject;
            $directory = $subject->directory;
            $subject = $subject->path;
        }

        if(is_string($subject)) {
            $subject = $this->open($subject);
        }

        $child = app(MediaService::class)->model([
            'context' => 'frame',
            'extension' => 'jpeg',
            'mime' => 'image/jpeg',
            'directory' => $directory,
            'disk' => app(MediaService::class)->config('temp.disk'),
        ]);

        $child->save();

        $path = pathinfo($child->relative_path, PATHINFO_DIRNAME);

        if(!app(MediaService::class)->storage()->disk($child->disk)->exists($path)) {
            app(MediaService::class)->storage()->disk($child->disk)->makeDirectory($path);
        }

        $subject->frame(TimeCode::fromSeconds($timeInSeconds))->save($child->path);
        
        if($parent) {
            $child->parent()->associate($parent);
        }

        $child->save();
        $child->encode();

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

    /**
     * Get the format of a video.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\Format
     */
    public function format(string $path): \FFMpeg\FFProbe\DataMapping\Format
    {
        return $this->ffprobe()->format($path);
    }

    /**
     * Get the tags of a video.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\Format
     */
    public function tags(string $path): array
    {
        return $this->ffprobe()->format($path)->get('tags');
    }

    /**
     * Get a tag frmo the video.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\Format
     */
    public function tag(string $path, $key)
    {
        return collect($this->tags($path))->get($key);
    }

    /**
     * Get the height of a video
     *
     * @param  string $path
     * @return int
     */
    public function height(string $path): int
    {
        return (int) $this->dimensions($path)->getHeight();
    }

    /**
     * Open a video file.
     *
     * @param  string $path
     * @return FFMpeg\Media\Video
     */
    public function open(string $path): Video
    {
        return $this->ffmpeg()->open($path);
    }

    /**
     * Get the streams from a video file.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\StreamCollection
     */
    public function streams(string $path): StreamCollection
    {
        return $this->ffprobe()->streams($path);
    }

    /**
     * Get the video streams from a path.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\StreamCollection
     */
    public function videos(string $path): StreamCollection
    {
        return $this->streams($path)->videos();
    }

    /**
     * Get the width of a video
     *
     * @param  string $path
     * @return int
     */
    public function width(string $path): int
    {
        return (int) $this->dimensions($path)->getWidth();
    }

    /**
     * Get the resolutions
     *
     * @param  \Objectivehtml\Medial\Model $model
     * @return Collection
     */
    public function resolutions(Model $model): Collection
    {
        $resolutions = app(VideoService::class)->config('video.resolutions');
        
        return collect($resolutions)
            ->filter(function($resolution) use ($model) {
                return $resolution['width'] < $model->width &&
                       $resolution['height'] < $model->height;
            })
            ->sort(function($a, $b) {
                return $a['width'] * $a['height'] < $b['width'] * $b['height'];
            });
    }

}