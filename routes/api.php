<?php


use Objectivehtml\Media\MediaService;

// Load some api routes...
Route::apiResource(
    app(MediaService::class)->config('rest.endpoint') ?: 'media',
    app(MediaService::class)->config('rest.controller') ?: 'MediaController'
);
