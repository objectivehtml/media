<?php

namespace Objectivehtml\Media\Services;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use FFMpeg\Coordinate\TimeCode;
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

        $child = $this->model([
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
            $this->ffmpeg = FFMpeg::create(array_merge($this->config('ffmpeg'), $config));
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
            $this->ffprobe = FFProbe::create(array_merge($this->config('ffmpeg'), $config));
        }

        return $this->ffprobe;
    }

    /**
     * Get the format of a video.
     *
     * @param  string $path
     * @return FFMpeg\FFProbe\DataMapping\Format
     */
    public function format($path): \FFMpeg\FFProbe\DataMapping\Format
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