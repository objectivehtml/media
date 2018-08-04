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

    public $width;

    public $height;

    public $colors = [
        '#000000'
    ];

    public function __construct($width = 1024, $height = 300, array $colors = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->colors = $colors ?: $this->colors;
    }

    public function apply(Model $model)
    {
        $audio = app(MediaService::class)->ffmpeg()->open($model->path);

        $child = app(MediaService::class)->model([
            'context' => 'waveform',
            'disk' => $model->disk,
            'directory' => $model->directory,
            'mime' => 'image/png',
            'extension' => 'png'
        ]);

        $child->parent()->associate($model);

        // Create the waveform
        $waveform = $audio->waveform($this->width, $this->height, $this->colors);

        $waveform->save($child->path);

        $child->size = filesize($child->path);
        $child->save();
    }

}
