<?php

namespace Tests\Unit\Model;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;

class MoveToDiskTest extends TestCase
{
    public function testCanMoveDiskFromLocalToS3()
    {
        $disk = app(MediaService::class)->config('temp.disk');

        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)->resource($file);
        
        $model = $resource->save();

        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo($disk));

        $model->moveToDisk('s3');

        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo('s3'));
        $this->assertFalse($model->storage()->disk($disk)->exists($model->relative_path));

    }

    public function testCanMoveDiskFromS3ToLocal()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)->resource($file)->disk('s3');

        $this->assertThat($resource->disk(), $this->equalTo('s3'));

        $model = $resource->save()->fresh();

        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo('s3'));

        $model->moveToDisk('local');

        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo('local'));
        $this->assertFalse($model->storage()->disk('s3')->exists($model->relative_path));
    }

}
