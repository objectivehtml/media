<?php

namespace Tests\Unit\Resources;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Resources\FileResource;

class StreamableResourceTest extends TestCase
{
    public function testDiskTest()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 1, 1);

        $resource = app(MediaService::class)->resource($file);
        
        $this->assertNull($resource->disk());

        $resource->disk('s3');
        
        $this->assertEquals('s3', $resource->disk());
    }
}
