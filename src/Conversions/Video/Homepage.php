<?php

namespace Objectivehtml\Media\Conversions\Video;

use Objectivehtml\Media\Model;

class Homepage extends EncodeVideo {

    public $options = [

        'audioCodec' => 'aac',

        'audioChannels' => 1,

        'audioKbps' => 56,

        'context' => 'homepage',

        'extension' => 'mp4',

        'height' => 720,

        'mime' => 'video/mp4',

        'muted' => false,

        'replace' => false,

        'threads' => 24,

        'timeout' => 0,

        'videoKbps' => 275,

        'width' => 1280

    ];

}
