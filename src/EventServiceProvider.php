<?php

namespace Objectivehtml\Media;

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
