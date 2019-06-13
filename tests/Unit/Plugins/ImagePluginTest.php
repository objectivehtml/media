<?php

namespace Tests\Unit\Plugins;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Resources\FileResource;

class ImagePluginTest extends TestCase
{
    public function testDefaultExtractingColorsFromImage()
    {   
        $resource = app(MediaService::class)
            ->resource(__DIR__.'/../../src/image.jpeg');

        $this->assertCount(3, $resource->save()->meta->get('colors'));
    }
    
    public function testCustomExtractingColorsFromImage()
    {   
        $resource = app(MediaService::class)
            ->resource(__DIR__.'/../../src/image.jpeg')
            ->extractColors(5);

        $this->assertCount(5, $resource->save()->meta->get('colors'));
    }
}
