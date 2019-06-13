<?php

namespace Tests\Unit\Resources;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Resources\FileResource;

class FileResourceTest extends TestCase
{
    public function testThatInstantiatingWorksTest()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 1, 1);

        $resource = app(MediaService::class)->resource($file);

        $this->assertInstanceOf(FileResource::class, $resource);
    }
    
    public function testThatSavingResourceWorksTest()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 1, 1);

        $resource = app(MediaService::class)->resource($file);

        $model = $resource->save();

        $this->assertThat($model->id, $this->equalTo(1));
        $this->assertTrue($model->fileExists);
    }
}
