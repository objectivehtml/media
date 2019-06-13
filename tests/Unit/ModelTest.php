<?php

namespace Tests\Unit;

use Media;
use Tests\TestCase;
use Objectivehtml\Media\Model;
use Illuminate\Http\UploadedFile;

class ModelTest extends TestCase
{
    public function testCreateModel()
    {        
        $file = UploadedFile::fake()->image('test.jpg', $width = 10, $height = 10);

        $model = Model::create($data = [
            'disk' => 'local',
            'directory' => '',
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->guessExtension(),
            'orig_filename' => $file->getClientOriginalName()
        ]);

        $this->assertTrue($model->exists);
    }

    public function testDeleteModel()
    {
        $file = UploadedFile::fake()->create('test.csv');

        $model = Media::resource($file)->save();

        Media::storage()->disk($model->disk)->assertExists($model->relative_path);

        $model->delete();

        Media::storage()->disk($model->disk)->delete($model->relative_path);

        Media::storage()->disk($model->disk)->assertMissing($model->relative_path);

        $this->assertCount(0, Media::storage()->disk($model->disk)->files($model->directory));
    }

    public function testExtensionSetAttribute()
    {
        $model = Model::create($data = [
            'disk' => 'local',
            'directory' => '',
            'filename' => 'test.mov'
        ]);

        $this->assertThat($model->filename, $this->equalTo('test.mov'));

        $model->extension = 'mp4';

        $this->assertThat($model->filename, $this->equalTo('test.mp4'));

        $model->filename = 'test.mov';

        $this->assertThat($model->filename, $this->equalTo('test.mov'));

    }
}
