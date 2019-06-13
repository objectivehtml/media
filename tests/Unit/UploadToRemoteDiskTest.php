<?php

namespace Tests\Unit;

use Media;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;

class UploadToRemoteDiskTest extends TestCase
{
    public function testThatModelUploadsToRemoteDiskAfterInitialProcessingTest()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 1, 1);

        $resource = app(MediaService::class)->resource($file);
        $resource->disk('s3');

        $model = $resource->save();

        $this->assertThat($model->id, $this->equalTo(1));
        $this->assertThat($model->disk, $this->equalTo('s3'));
        $this->assertTrue($resource->storage()->disk('s3')->exists($model->relative_path));
        $this->assertFalse($resource->storage()->disk('public')->exists($model->relative_path));
    }
    
}
