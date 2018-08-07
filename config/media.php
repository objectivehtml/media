<?php

use Objectivehtml\Media\Model;
use Geocoder\Provider\Chain\Chain;
use Objectivehtml\Media\Jobs\ExtractFrames;
use Objectivehtml\Media\Plugins\AudioPlugin;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Objectivehtml\Media\Plugins\ImagePlugin;
use Objectivehtml\Media\Plugins\VideoPlugin;
use Objectivehtml\Media\Policies\MediaPolicy;
use Objectivehtml\Media\Conversions\Audio\Waveform;
use Objectivehtml\Media\Plugins\GeocoderPlugin;
use Objectivehtml\Media\Conversions\Image\Thumbnail;
use Objectivehtml\Media\Strategies\FilenameStrategy;
use Objectivehtml\Media\Strategies\DirectoryStrategy;
use Objectivehtml\Media\Conversions\PreserveOriginal;
use Objectivehtml\Media\Conversions\Video\EncodeForWeb;
use Objectivehtml\Media\Conversions\Image\ResizeMaxDimensions;
use Objectivehtml\Media\Strategies\ModelMatchingStrategy;
use Objectivehtml\Media\Http\Controllers\MediaController;
use Objectivehtml\Media\Strategies\ObfuscatedDirectoryStrategy;

use Objectivehtml\Media\Filters\Image\Greyscale;

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
     * Should prevent duplicate media uploads by default. If this is true,
     * resources will only be saved onces, and anytime the upload methods
     * are called, the existing model will return.
     */
    'prevent_duplicates' => true,

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
        VideoPlugin::class,
        GeocoderPlugin::class,
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

        // The model matching strategy.
        'matching' => ModelMatchingStrategy::class,
    ],

    /**
     * The RESTful endpoint settings
     */

    'rest' => [

        // The request input key
        'key' => 'file',

        // The resource api endpoint
        'endpoint' => 'api/media',

        // The resource api controller class
        'controller' => MediaController::class,

        'policy' => MediaPolicy::class,

        'middleware' => [

            /*
            'auth:api' => [
                'except' => ['index', 'show']
            ]
            */

        ],

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

        // The conversions that should be applied to all images.
        'filters' => [
            //
        ],

        // The conversions that should be applied to all images.
        'conversions' => [
            [PreserveOriginal::class],
            [ResizeMaxDimensions::class, [env('IMAGES_MAX_WIDTH', 2048), env('IMAGES_MAX_HEIGHT', 1536)]],
            [Thumbnail::class, [env('IMAGES_THUMB_WIDTH', 100), env('IMAGES_THUMB_HEIGHT', 100)]]
        ],

        // Supports GD or Imagic
        'driver' => 'imagick',

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
            [PreserveOriginal::class],
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

        // Extract the first frame synchronously so it is available immediately
        // in the model or API response.
        'sync_extract_first_frame' => true,

        // By default the starting time (in seconds) is set to 30 because the
        // first frame is extract synchronously. If that is set to false, this
        // option should be set to 0 if you want the first frame.
        'extract_frames_starting' => 30,

        // Extract frame every X seconds.
        'extract_frames_every' => 30,

        'conversions' => [
            [PreserveOriginal::class],
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

    ],

    /**
     * The geocoder plugin to automatically geolocate the meta data in your
     * images.
     */
    'geocoder' => [

        'locale' => 'en',

        'api_key' => env('GOOGLE_MAPS_API_KEY'),

        'providers' => [
            Chain::class => [
                GoogleMaps::class => [
                    env('GOOGLE_MAPS_LOCALE', 'en-US'),
                    env('GOOGLE_MAPS_API_KEY'),
                ],
            ],
        ],

        'cache' => [

            /*
            |-----------------------------------------------------------------------
            | Cache Store
            |-----------------------------------------------------------------------
            |
            | Specify the cache store to use for caching. The value "null" will use
            | the default cache store specified in /config/cache.php file.
            |
            | Default: null
            |
            */

            'store' => null,

            /*
            |-----------------------------------------------------------------------
            | Cache Duration
            |-----------------------------------------------------------------------
            |
            | Specify the cache duration in minutes. The default approximates a
            | "forever" cache, but there are certain issues with Laravel's forever
            | caching methods that prevent us from using them in this project.
            |
            | Default: 9999999 (integer)
            |
            */

            'duration' => 9999999,

        ]

    ]

];
