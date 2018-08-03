<?php

use Objectivehtml\MediaManager\Model;
use Objectivehtml\MediaManager\Plugins\AudioPlugin;
use Objectivehtml\MediaManager\Plugins\ImagePlugin;
use Objectivehtml\MediaManager\Plugins\VideoPlugin;
use Objectivehtml\MediaManager\Conversions\Audio\Waveform;
use Objectivehtml\MediaManager\Conversions\Image\Thumbnail;
use Objectivehtml\MediaManager\Strategies\FilenameStrategy;
use Objectivehtml\MediaManager\Strategies\DirectoryStrategy;
use Objectivehtml\MediaManager\Conversions\Video\EncodeForWeb;
use Objectivehtml\MediaManager\Strategies\ObfuscatedDirectoryStrategy;

return [

    /**
     * The Media Eloquent model to use.
     */

    'model' => Model::class,

    /**
     * The default storage disk.
     */

    'disk' => config('MEDIA_DISK', 'public'),

    /**
     * Settings that apply to temp files, which are files that have been
     * uploaded but are still being processed.
     */

    'temp' => [
        'disk' => config('MEDIA_TEMP_DISK', 'public')
    ],

    /**
     * Should preserve the original file by default.
     */
    'preserve_original' => true,

    /**
     * The installed plugins.
     */

    'plugins' => [
        AudioPlugin::class,
        ImagePlugin::class,
        VideoPlugin::class
    ],

    /**
     * FFMpeg settings. These are required for videos.
     */

    'ffmpeg' => [
        'timeout' => env('FFMPEG_TIMEOUT', 999999),
        'ffmpeg.threads' => env('FFMPEG_THREADS', 12),
        'ffmpeg.binaries' => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
        'ffprobe.binaries' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),
    ],

    /**
     * The default strategies.
     */

    'strategies' => [
        // The directory structure will be saved with the format: {id}/filename.jpeg
        'directory' => DirectoryStrategy::class,

        // The directory structure will be saved with the format: {hash}/filename.jpeg
        // 'directory' => ObfuscatedDirectoryStrategy::class,

        // The filename generation strategy.
        'filename' => FilenameStrategy::class,
    ],

    /**
     * Image plugin settings.
     */

    'image' => [

        'max_width' => env('IMAGES_MAX_WIDTH', 2048),

        'max_height' => env('IMAGES_MAX_HEIGHT', 1536),

        'conversions' => [
            [Thumbnail::class, [env('IMAGES_THUMB_WIDTH', 100), env('IMAGES_THUMB_HEIGHT', 100)]]
        ],

        'mimes' => [
            'image/bmp',
            'image/gif',
            'image/jpeg',
            'image/tiff',
            'image/x-icon',
        ],

        'extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'tif', 'bmp', 'ico', 'psd', 'webp'
        ]

    ],

    /**
     * Audio plugin settings.
     */
    'audio' => [

        'conversions' => [
            [Waveform::class]
        ],

        'mimes' => [
            'audio/basic',
            'audio/mid',
            'audio/mpeg',
            'audio/mp3',
            'audio/mp4',
            'audio/vnd.wav',
            'audio/vorbis',
            'audio/ogg',
            'audio/x-aiff',
            'audio/x-m4a',
        ],

        'extensions' => [
            'mid', 'mp3', 'm4a', 'wav', 'pcm', 'aiff', 'aac', 'wma'
        ]

    ],

    /**
     * Video plugin settings.
     */
    'video' => [

        'conversions' => [
            [EncodeForWeb::class]
        ],

        'mimes' => [
            'video/mp4',
            'video/x-m4v',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv'
        ],

        'extensions' => [
            'mp4', 'mv4', 'mov', 'avi', 'wmv'
        ],

        // Extract frame every X seconds
        'extract_frames' => 30,

        'resolutions' => [[
            'width' => 256,
            'height' => 144,
            'videoKbps' => 500
        ],[
            'width' => 640,
            'height' => 360,
            'videoKbps' => 750
        ],[
            'width' => 1280,
            'height' => 720,
            'videoKbps' => 1500
        ]]

    ]

];
