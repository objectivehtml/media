<?php

namespace Tests\Feature;

use Media;
use Tests\TestCase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Contracts\Filesystem\Factory;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\MediaServiceProvider;
use Objectivehtml\Media\Filters\Image\Crop;
use Objectivehtml\Media\Facades\Media as Facade;
use Objectivehtml\Media\Filters\Image\Greyscale;
use Objectivehtml\Media\Conversions\Image\Thumbnail;
use Objectivehtml\Media\Contracts\Strategy as StrategyInterface;

class MediaEndpointTest extends TestCase
{

    public function testIndex()
    {
        $response = $this->get(Media::config('rest.endpoint'));

        $response->assertStatus(200);
    }

    public function testStore()
    {
        $response = $this
            ->actingAs($this->user())
            ->post(Media::config('rest.endpoint'), [
                'file' => UploadedFile::fake()->image('test.jpg', $width = 10, $height = 10),
                'meta' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ]
            ]);

        $response->assertStatus(200);

        $model = Model::first();

        $this->assertTrue($model->fileExists);
        $this->assertCount(7, $model->meta);
    }

    public function testShow()
    {
        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this
            ->actingAs($this->user())
            ->get(Media::config('rest.endpoint').'/'.$model->id);

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this
            ->actingAs($this->user())
            ->put(Media::config('rest.endpoint').'/'.$model->id, [
                'title' => 'test'
            ]);

        $response->assertStatus(200);

        $this->assertThat($model::find($model->id)->title, $this->equalTo('test'));
    }

    public function testDelete()
    {
        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this
            ->actingAs($this->user())
            ->delete(Media::config('rest.endpoint').'/'.$model->id);

        $response->assertStatus(200);

        $this->assertNull($model::find($model->id));
    }


}
