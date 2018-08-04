<?php

namespace Objectivehtml\Media\Conversions\Video;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Jobs\EncodeVideo;
use Objectivehtml\Media\Jobs\ExtractFrames;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Conversions\Conversion;
use Objectivehtml\Media\Jobs\CopyAndEncodeVideo;
use Objectivehtml\Media\Contracts\StreamableResource;;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class EncodeForWeb extends Conversion implements ConversionInterface {

    public function apply(Model $model)
    {
        $resolutions = array_filter(app(MediaService::class)->config('video.resolutions'), function($item) use ($model) {
            return $item['width'] < $model->meta->get('width') && $item['height'] < $model->meta->get('height');
        });

        usort($resolutions, function($a, $b) {
            return $a['width'] * $a['height'] < $b['width'] * $b['height'];
        });

        $jobs = collect([
            new EncodeVideo($model)
        ])->concat(array_map(function($options) use ($model) {
            return new CopyAndEncodeVideo($model, $options);
        }, $resolutions));

        ExtractFrames::withChain($jobs)->dispatch(
            $model,
            app(MediaService::class)->config('video.extract_frames'),
            app(MediaService::class)->config('video.extract_frames')
        );
    }

    public function applyToMimes(): array
    {
        return [
            'video/mp4',
            'video/x-m4v',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
        ];
    }

    public function applyToExtensions(): array
    {
        return [
            'mp4', 'mv4', 'mov', 'avi', 'wmv'
        ];
    }

}
