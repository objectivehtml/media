<?php

namespace Objectivehtml\Media\Plugins;

use Exception;
use Carbon\Carbon;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToVideos;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Services\VideoService;
use Objectivehtml\Media\Conversions\Video\EncodeVideo;
use Objectivehtml\Media\Strategies\ConfigClassStrategy;
use Objectivehtml\Media\Conversions\Video\ExtractFrames;

class VideoPlugin extends Plugin {

    use Applyable, ApplyToVideos;

    public function created(Model $model)
    {
        if($model->fileExists) {
            if(!$model->meta->get('width') && !$model->meta->get('height')) {
                $model->meta('width', $width = app(VideoService::class)->width($model->path));
                $model->meta('height', $height = app(VideoService::class)->height($model->path));
                $model->meta('aspect_ratio', app(VideoService::class)->aspectRatio($width, $height));
            }

            if(!$model->meta->get('duration')) {
                $model->meta('duration', app(VideoService::class)->duration($model->path));
            }

            if(!$model->meta->get('bit_rate')) {
                $model->meta('bit_rate', app(VideoService::class)->bitRate($model->path));
            }

            if($model->exif && ($model->exif->DateTimeOriginal || $model->exif->DateTime)) {
                try {
                    $model->taken_at = Carbon::parse($model->exif->DateTimeOriginal ?: $model->exif->DateTime);
                }
                catch(Exception $e) {
                    //
                }
            }
            
            if($taken_at = app(VideoService::class)->tag($model->path, 'creation_time')) {
                $model->taken_at = Carbon::parse($taken_at);
            }

            $model->save();

            if(app(VideoService::class)->config('video.sync_extract_first_frame') && $model->isParent()) {
                app(VideoService::class)->extractFrame($model);
            }
        }
    }

    public function jobs(Model $model): array
    {
        return ConfigClassStrategy::map(app(MediaService::class)->config('video.jobs', []), $model);
    }

    public function filters(Model $model): array
    {
        return ConfigClassStrategy::map(app(MediaService::class)->config('video.filters', []));
    }

    public function conversions(Model $model): array
    {
        return ConfigClassStrategy::collect(app(MediaService::class)->config('video.conversions', []))
            ->concat([
                new ExtractFrames(),
                new EncodeVideo([
                    'replace' => true
                ])
            ])
            ->concat(
                app(VideoService::class)
                    ->resolutions($model)
                    ->map(function($options) {
                        return new EncodeVideo($options);
                    })
            )
            ->all();
    }

    public function doesApply($mime, $extension): bool
    {
        return explode('/', $mime)[0] === 'video' || parent::doesApply($mime, $extension);
    }

    public function doesMeetRequirements(): bool
    {
        if(class_exists('FFMpeg\FFMpeg')) {
            try {
                $ffmpeg = \FFMpeg\FFMpeg::create(app(MediaService::class)->config('ffmpeg', []));
            }
            catch(Exception $e) {
                return false;
            }

            return true;
        }

        return false;
    }

}
