<?php

namespace Tests\Feature;

use PDOException;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Intervention\Image\ImageManagerStatic as Image;

class DatabaseTest extends TestCase
{

    public function testCreateModel()
    {
        $file = UploadedFile::fake()->image('test.jpg', $width = 10, $height = 10);

        try {
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
        catch(PDOException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMetaCrudOnModel()
    {
        $file = UploadedFile::fake()->image('test.jpg', $width = 10, $height = 10);

        $model = new Model([
            'disk' => 'local',
            'directory' => '',
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->guessExtension(),
            'orig_filename' => $file->getClientOriginalName()
        ]);

        $this->assertInstanceOf(Collection::class, $model->meta);
        $this->assertCount(0, $model->meta);

        $model->meta = [
            'a' => 1
        ];

        $this->assertThat($model->meta->get('a'), $this->equalTo(1));

        $model->meta('a', null);
        $model->meta('b', 2);
        $model->meta([
            'c' => 3,
            'd' => 4,
            'e' => null,
            'f' => null
        ]);

        $model->tag(1, 2, 3);
        $model->save();
        $model->tag('image');

        $this->assertCount(4, $model->tags);
        $this->assertArrayHasKey('b', $model->toArray()['meta']);
        $this->assertArrayHasKey('c', $model->toArray()['meta']);
        $this->assertArrayHasKey('d', $model->toArray()['meta']);
    }

    public function testDeleteModel()
    {
        $file = UploadedFile::fake()->create('test.csv');

        $model = app(MediaService::class)->resource($file)->save();

        app(MediaService::class)->storage()->disk($model->disk)->assertExists($model->relative_path);

        $model->delete();

        app(MediaService::class)->storage()->disk($model->disk)->delete($model->relative_path);

        app(MediaService::class)->storage()->disk($model->disk)->assertMissing($model->relative_path);

        $this->assertCount(0, app(MediaService::class)->storage()->disk($model->disk)->files($model->directory));
    }

}
