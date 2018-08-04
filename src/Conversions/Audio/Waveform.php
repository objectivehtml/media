<?php

namespace Objectivehtml\Media\Conversions\Audio;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Conversions\Conversion;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Contracts\StreamableResource;;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;
use Objectivehtml\Media\Support\ApplyToAudio;

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
