<?php

use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Conversions\Audio\Waveform;
use Objectivehtml\Media\Conversions\PreserveOriginal;
use Objectivehtml\Media\Http\Controllers\MediaController;
use Objectivehtml\Media\Jobs\ExtractColorPalette;
use Objectivehtml\Media\Macros\UploadedFile\ModelMacro;
use Objectivehtml\Media\Macros\UploadedFile\ResourceMacro;
use Objectivehtml\Media\Plugins\AudioPlugin;
use Objectivehtml\Media\Plugins\ImagePlugin;
use Objectivehtml\Media\Plugins\VideoPlugin;
use Objectivehtml\Media\Policies\MediaPolicy;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Strategies\DirectoryStrategy;
use Objectivehtml\Media\Strategies\FilenameStrategy;
use Objectivehtml\Media\Strategies\ModelMatchingStrategy;
use Objectivehtml\Media\TemporaryModel;

return [

    'aliases' => [
        MediaService::class => 'media' 
    ],

    /**
     * The Media Eloquent model to use.
     */
    'model' => Model::class,

    /**
     * The default storage disk.
     */
    'disk' => env('MEDIA_DISK', 'public'),

    /**
     * Settings that apply to temp files, which are files that have been
     * uploaded but are still being processed.
     */
    'temp' => [

        'disk' => env('MEDIA_TEMP_DISK', 'public'),

        'model' => TemporaryModel::class,

        'context' => '__temp__',

        'delay' => 3600,
        
    ],

    /**
     * The macros that are applied. Should be an array of key/values with the
     * key being the class you want to apply the macro to, and the value being
     * another array of key/values. The key being the method name, and the value
     * should be the name of an invokeable class.
     */
    'macros' => [
        UploadedFile::class => [
            'model' => ModelMacro::class,
            'resource' => ResourceMacro::class
        ]
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
     * If true, the library with handle the saved and deleted observers so that
     * media is automatically attached/synced to the models that implements the
     * Objectivehtml\Media\Mediable trait. Setting this false will disable the
     * default functionality and leave it up to you to implement this
     * functionality within your app.
     */
    'use_observer' => true,

    /**
     * If true, the media in the request with be synced with the associated
     * model, whereas false would always attach them and never remove then.
     * This config is great for REST controllers that need to associate media
     * to existing models and want to have full CRUD of the media.
     */
    'use_sync' => true,

    /**
     * Request input key(s) that should be use to handle the uploads when
     * uploading files from a request using the `addMediaFromRequest()` method.
     * If null, then all files in the request will be added regardless of the
     * key. If an array is provided, all keys in the array will be used. And
     * obviously if a single key is given, then only files from that key will
     * apply.
     */
    'request' => ['file', 'files'],

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
        'ffmpeg.binaries' => env('FFMPEG_BINARIES'),
        'ffprobe.binaries' => env('FFPROBE_BINARIES'),
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

        // The relationships to eargerly load in the HTTP requests.
        'with' => 'children',

        // The request input key to for file uploads in the rest controller.
        'input' => 'file',

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
        
        'jobs' => [
            ExtractColorPalette::class
        ],

        // The conversions that should be applied to all images.
        /*
        'filters' => [
            [ResizeMaxDimensions::class, [
                env('IMAGES_MAX_WIDTH', 2048),
                env('IMAGES_MAX_HEIGHT', 1536)
            ]]
        ],
        */

        // The conversions that should be applied to all images.
        /*
        'conversions' => [
            [PreserveOriginal::class],
            [Thumbnail::class, [
                env('IMAGES_THUMB_WIDTH', 100),
                env('IMAGES_THUMB_HEIGHT', 100)
            ]]
        ],
        */

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
        'extract_frames_starting_at' => 30,

        // Extract frame every X seconds.
        'extract_frames_interval' => 30,

        // The context value that is given to videos that have been encoded.
        'encoded_context_key' => 'encoded',

        'conversions' => [
            [PreserveOriginal::class]
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
            GoogleMaps::class => [
                env('GOOGLE_MAPS_LOCALE', 'en-US'),
                env('GOOGLE_MAPS_API_KEY'),
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
