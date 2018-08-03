<?php

namespace Objectivehtml\MediaManager\Facades;

use Illuminate\Support\Facades\Facade;
use Objectivehtml\MediaManager\MediaService;

class Media extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MediaService::class;
    }

}
