<?php

namespace Objectivehtml\Media;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManagerStatic as Image;


class MediaServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/media.php', 'media'
        );

        $this->app->singleton(MediaService::class, function($app) {
            return new MediaService($app->filesystem, $app['config']['media']);
        });
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
            'driver' => 'imagick'
        ]);
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
