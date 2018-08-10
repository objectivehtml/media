<?php

namespace Objectivehtml\Media\Plugins;

use FFMpeg\FFMpeg;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToVideos;
use FFMpeg\Exception\ExecutableNotFoundException;
use Objectivehtml\Media\Conversions\Video\EncodeVideo;
use Objectivehtml\Media\Conversions\Video\ExtractFrames;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Strategies\JobsConfigClassStrategy;

class VideoPlugin extends Plugin {

    use Applyable, ApplyToVideos;

    public function created(Model $model)
    {
        if($this->doesApplyToModel($model) && $model->fileExists) {
            if(!$model->meta->get('width') && !$model->meta->get('height')) {
                $model->meta('width', $width = app(MediaService::class)->width($model->path));
                $model->meta('height', $height = app(MediaService::class)->height($model->path));
                $model->meta('aspect_ratio', app(MediaService::class)->aspectRatio($width, $height));
            }
            if(!$model->meta->get('duration')) {
                $model->meta('duration', app(MediaService::class)->duration($model->path));
            }
            if(!$model->meta->get('bit_rate')) {
                $model->meta('bit_rate', app(MediaService::class)->bitRate($model->path));
            }

            $model->save();

            if(app(MediaService::class)->config('video.sync_extract_first_frame') && $model->isParent()) {
                app(MediaService::class)->extractFrame($model);
            }
        }
    }

    public function resolutions(Model $model): array
    {
        $resolutions = collect(app(MediaService::class)->config('video.resolutions', []))
            ->filter(function($resolution) use ($model) {
                return $resolution['width'] < $model->meta->get('width') &&
                       $resolution['height'] < $model->meta->get('height');
            })
            ->sort(function($a, $b) {
                return $a['width'] * $a['height'] < $b['width'] * $b['height'];
            });

        return $resolutions->all();
    }

    public function jobs(Model $model): array
    {
        return array_map(
            JobsConfigClassStrategy::make($model),
            app(MediaService::class)->config('video.jobs', [])
        );
    }

    public function filters(Model $model): array
    {
        return array_map(
            ConfigClassStrategy::make(),
            app(MediaService::class)->config('video.filters', [])
        );
    }

    public function conversions(Model $model): array
    {
        $conversions = array_map(
            ConfigClassStrategy::make(),
            app(MediaService::class)->config('video.conversions', [])
        );

        return collect($conversions)
            ->concat([
                new ExtractFrames(),
                new EncodeVideo([
                    'replace' => true
                ])
            ])
            ->concat(
                array_map(function($options) {
                    return new EncodeVideo($options);
                }, $this->resolutions($model))
            )
            ->all();
    }

    public function doesApply($mime, $extension): bool
    {
        return explode('/', $mime)[0] === 'video' || parent::doesApply($mime, $extension);
    }

    public function doesMeetRequirements(): bool
    {
        try {
            $ffmpeg = FFMpeg::create(app(MediaService::class)->config('ffmpeg'));
        }
        catch(ExecutableNotFoundException $e) {
            return false;
        }

        return true;
    }

}
