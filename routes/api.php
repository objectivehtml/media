<?php

use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Http\Controllers\MediaController;

// Load some api routes...

if(app(MediaService::class)->config('rest.endpoint')) {
    Route::apiResource(
        app(MediaService::class)->config('rest.endpoint'),
        app(MediaService::class)->config('rest.controller') ?: MediaController::class
    );

    Route::put(app(MediaService::class)->config('rest.endpoint').'/{id}/favorite', MediaController::class.'@favorite');
    Route::put(app(MediaService::class)->config('rest.endpoint').'/{id}/unfavorite', MediaController::class.'@unfavorite');
    Route::put(app(MediaService::class)->config('rest.endpoint').'/{id}/encode', MediaController::class.'@encode');
}
