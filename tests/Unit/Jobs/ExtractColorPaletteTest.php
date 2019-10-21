<?php

namespace Tests\Unit\Model;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;

class ExtractColorPaletteTest extends TestCase
{
    public function testExtractColorPaletteTest()
    {
        
        $resource = app(MediaService::class)->resource(base_path('tests/src/image.jpeg'));
        
        dd($resource);
        
        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo($disk));

        dd($model);
        
        $model->moveToDisk('s3');

        $this->assertTrue($model->fileExists);
        $this->assertThat($model->disk, $this->equalTo('s3'));
        $this->assertFalse($model->storage()->disk($disk)->exists($model->relative_path));

    }

}
