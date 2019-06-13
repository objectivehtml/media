<?php

namespace Objectivehtml\Media;

use Illuminate\Support\Facades\Gate;
use Objectivehtml\Media\Plugins\ImagePlugin;
use Objectivehtml\Media\Plugins\VideoPlugin;
use Objectivehtml\Media\Services\ImageService;
use Objectivehtml\Media\Services\MediaService;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Events\MovedModelToDisk::class => [
            Listeners\MovedModelToDiskListener::class
        ]
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        parent::boot();
    }
}
