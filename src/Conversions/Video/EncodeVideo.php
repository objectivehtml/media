<?php

namespace Objectivehtml\Media\Conversions\Video;

use FFMpeg\Media\Video;
use FFMpeg\Format\Video\X264;
use Objectivehtml\Media\Model;
use FFMpeg\Coordinate\Dimension;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\ApplyToVideos;
use Objectivehtml\Media\Conversions\Conversion;
use Objectivehtml\Media\Events\VideoEncodingStarted;
use Objectivehtml\Media\Events\VideoEncodingFinished;
use Objectivehtml\Media\Events\VideoEncodingProgressed;
use Objectivehtml\Media\Exceptions\FileNotFoundException;
use Objectivehtml\Media\Exceptions\FilePathsCannotMatchException;
use Objectivehtml\Media\Exceptions\CannotFindOriginalFileException;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class EncodeVideo extends Conversion implements ConversionInterface {

    use ApplyToVideos;

    public $options = [

        'audioCodec' => 'aac',

        'audioChannels' => 2,

        'audioKbps' => 256,

        'context' => null,

        'extension' => 'mp4',

        'height' => null,

        'mime' => 'video/mp4',

        'muted' => false,

        'replace' => false,

        'threads' => 24,

        'timeout' => 0,

        'videoKbps' => 1000,

        'width' => null

    ];

    public function __construct(array $options = null)
    {
        if($options) {
            $this->options = array_merge($this->options, $options);
        }
    }

    public function __get($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    public function apply(Model $model)
    {
        $subject = $this->subject($model);

        event(new VideoEncodingStarted($subject));

        $this->encode($subject, $this->video($model), $model);

        event(new VideoEncodingFinished($subject));
    }

    public function encode(Model $model, Video $video, $parent)
    {
        if($this->replace) {
            $model->mime = $this->mime;
            $model->extension = $this->extension;
        }

        $video->save($this->format($model), $model->path);

        $model->meta('encoding', false);
        $model->meta('encoded', true);
        $model->size = app(MediaService::class)->storage()->disk($model->disk)->size($model->relative_path);
        $model->save();
    }

    public function subject(Model $model)
    {
        if($this->replace) {
            $model->context = $this->context ?: $model->context;
            $model->meta('encoding', true);
            $model->save();

            return $model;
        }

        $child = $model::make([
            'context' => $this->context ?: app(MediaService::class)->config('video.encoded_context_key', 'encoded'),
            'extension' => $this->extension,
            'directory' => $model->directory,
            'orig_filename' => $model->orig_filename,
            'mime' => $this->mime,
            'meta' => [
                'encoding' => true,
                'width' => $this->width,
                'height' => $this->height
            ]
        ]);

        $child->parent()->associate($model);
        $child->save();

        return $child;
    }

    public function video(Model $model): Video
    {
        $path = $this->extractOriginalPath($model);

        $video = app(MediaService::class)->ffmpeg()->open($path);

        if($this->width && $this->height) {
            $video->filters()
                ->resize(new Dimension($this->width, $this->height))
                ->synchronize();
        }

        return $video;
    }

    public function format(Model $model)
    {
        $format = (new X264($this->audioCodec))
            ->setAdditionalParameters(['-strict', '-2'])
            ->setKiloBitrate($this->videoKbps)
            ->setAudioChannels($this->audioChannels)
            ->setAudioKiloBitrate($this->audioKbps)
            ->on('progress', function($video, $format, $percentage) use ($model) {
                $model->meta('encoded_percent', $percentage);
                $model->save();

                event(new VideoEncodingProgressed($model));
            });

        if($this->muted) {
            $format->setAdditionalParameters(['-an']);
        }

        return $format;
    }

    public function ensureFileExists(string $path)
    {
        if(!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
    }

    public function ensurePathsDoNotMatch(Model $model, $path)
    {
        if($model->path === $path) {
            throw new FilePathsCannotMatchException;
        }
    }

    public function extractOriginalPath(Model $model)
    {
        if($original = $model->children()->original()->first()) {
            $path = $original->path;
        }
        else if($model->parent && ($original = $model->parent->children()->original()->first())) {
            $path = $original->path;
        }
        else if($model->parent) {
            $path = ($model->parent->children()->original()->first() ?: $model->parent)->path;
        }
        else {
            throw new CannotFindOriginalFileException;
        }

        $this->ensureFileExists($path);
        $this->ensurePathsDoNotMatch($model, $path);

        return $path;
    }

}