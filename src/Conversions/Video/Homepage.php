<?php

namespace Objectivehtml\Media\Conversions\Video;

use Objectivehtml\Media\Model;

class Homepage extends EncodeVideo {

    public $options = [

        'audioCodec' => 'aac',

        'audioChannels' => 1,

        'audioKbps' => 56,

        'extension' => 'mp4',

        'height' => 576,

        'mime' => 'video/mp4',

        'muted' => true,

        'replace' => false,

        'threads' => 24,

        'timeout' => 0,

        'videoKbps' => 300,

        'width' => 1280

    ];

}
