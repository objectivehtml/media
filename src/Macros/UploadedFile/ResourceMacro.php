<?php

namespace Objectivehtml\Media\Macros\UploadedFile;

use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;

class ResourceMacro
{
    public function __invoke(UploadedFile $file)
    {
        return app(MediaService::class)->resource($file);        
    }   
}