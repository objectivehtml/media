<?php

use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Http\Controllers\MediaController;

// Load some api routes...

if(app(MediaService::class)->config('rest.endpoint')) {
    Route::apiResource(
        app(MediaService::class)->config('rest.endpoint'),
        app(MediaService::class)->config('rest.controller') ?: MediaController::class
    );
}
