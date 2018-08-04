<?php

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Plugins\AudioPlugin;
use Objectivehtml\Media\Plugins\ImagePlugin;
use Objectivehtml\Media\Plugins\VideoPlugin;
use Objectivehtml\Media\Conversions\Audio\Waveform;
use Objectivehtml\Media\Conversions\Image\Thumbnail;
use Objectivehtml\Media\Strategies\FilenameStrategy;
use Objectivehtml\Media\Strategies\DirectoryStrategy;
use Objectivehtml\Media\Conversions\Video\EncodeForWeb;
use Objectivehtml\Media\Strategies\ObfuscatedDirectoryStrategy;

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
     * Should delete the empty directories when a model and its assets are
     * deleted.
     */
    'delete_directories' => true,

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
     * The RESTful endpoint settings
     */
    'rest' => [
        // The request input key
        'key' => 'file',

        // The endpoint resource slug
        'endpoint' => 'media',

        // The resource api controller class
        'controller' => 'Objectivehtml\\Media\\Http\\Controllers\\MediaController',

        'rules' => [

            // The validation rules for "storing" models
            'store' => [
                'file' => 'required|file'
            ],

            // The validation rules for "updating" models
            'update' => [
                //
            ]

        ]
    ],

    /**
     * Image plugin settings.
     */

    'image' => [

        // The maximum width for all images
        'max_width' => env('IMAGES_MAX_WIDTH', 2048),

        // The maximum height for all images
        'max_height' => env('IMAGES_MAX_HEIGHT', 1536),

        // The conversions that should be applied to all images.
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
        ],

        'colors' => [

            // Extract the X most representative colors
            'total' => 3,

            // Max max width of the source image used to calculate the color.
            // Larger images require more time and memory to calculate the color.
            'max_width' => 600,

            // Max height width of the source image used to calculate the color.
            'max_height' => 600,

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
