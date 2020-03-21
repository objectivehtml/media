<?php

namespace Objectivehtml\Media\Macros\UploadedFile;

use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;

class ModelMacro
{
    public function __invoke(UploadedFile $file, array $attributes = [])
    {
        return app(MediaService::class)
            ->resource($file)
            ->model($attributes);
    } 
}