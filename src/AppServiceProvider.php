<?php

namespace Objectivehtml\Media;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Objectivehtml\Media\Plugins\ImagePlugin;
use Objectivehtml\Media\Plugins\VideoPlugin;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Services\VideoService;
use Intervention\Image\Facades\Image as Image;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/media.php', 'media'
        );

        $this->app->singleton(MediaService::class, function($app) {
            return new MediaService($app->filesystem, $app['config']['media']);
        });

        if(app(MediaService::class)->isPluginInstalled(ImagePlugin::class)) {
            $this->app->singleton(ImageService::class, function($app) {
                return new ImageService($app->filesystem, $app['config']['media']);
            });
        }

        if(app(MediaService::class)->isPluginInstalled(VideoPlugin::class)) {
            $this->app->singleton(VideoService::class, function($app) {
                return new VideoService($app->filesystem, $app['config']['media']);
            });
        }
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media.php')
        ], 'config');

        // Set the image configuration defaults.
        Image::configure([
            'driver' => app(MediaService::class)->config('image.driver')
        ]);

        if($policy = app(MediaService::class)->config('rest.policy')) {
            Gate::policy(app(MediaService::class)->config('model'), $policy);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MediaService::class
        ];
    }
}
