<?php

namespace Tests\Unit\Conversions\Image;

use Tests\TestCase;
use Objectivehtml\Media\Model;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Conversions\Image\Thumbnail;

class ThumnailTest extends TestCase
{
    public function testConvertingImageToThumbnail()
    {
        $file = UploadedFile::fake()->image('test.jpg', 500, 500);

        $resource = app(MediaService::class)->resource($file);
        $resource->conversion(new Thumbnail(100, 100));
        
        $model = $resource->save();

        $this->assertInstanceOf(Model::class, $model->children->first());
        $this->assertTrue($model->id !== $model->children->first()->id);
        $this->assertThat($model->children->first()->context, $this->equalTo('thumbnail'));

    }

}
