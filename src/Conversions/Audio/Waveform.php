<?php

namespace Objectivehtml\MediaManager\Conversions\Audio;

use Objectivehtml\MediaManager\Model;
use Objectivehtml\MediaManager\MediaService;
use Objectivehtml\MediaManager\Conversions\Conversion;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\MediaManager\Contracts\StreamableResource;;
use Objectivehtml\MediaManager\Contracts\Conversion as ConversionInterface;
use Objectivehtml\MediaManager\Support\ApplyToAudio;

class Waveform extends Conversion implements ConversionInterface {

    use ApplyToAudio;

    public function apply(Model $model)
    {
        $audio = app(MediaService::class)->ffmpeg()->open($model->path);

        $child = app(MediaService::class)->model([
            'context' => 'waveform',
            'disk' => $model->disk,
            'directory' => $model->directory,
            'extension' => 'png'
        ]);

        $child->parent()->associate($model);

        // Create the waveform
        $waveform = $audio->waveform();
        $waveform->save($child->path);

        $child->size = filesize($child->path);
        $child->save();
    }

}
