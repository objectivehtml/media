<?php

namespace Objectivehtml\Media\Facades;

use Illuminate\Support\Facades\Facade;
use Objectivehtml\Media\MediaService;

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
