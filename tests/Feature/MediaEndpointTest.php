<?php

namespace Tests\Feature;

use Media;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
        $response = $this->get('media');

        $response->assertStatus(200);
    }

    public function testStore()
    {
        $this->be(app(\Illuminate\Foundation\Auth\User::class));

        $response = $this->post('media', [
            'file' => UploadedFile::fake()->image('test.jpg', $width = 10, $height = 10),
            'meta' => [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        ]);

        dd($response->getContent());

        $response->assertStatus(200);
    }

    public function testShow()
    {
        $this->be(app(\Illuminate\Foundation\Auth\User::class));

        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this->get('media/'.$model->id);

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this
            ->actingAs(app(\Illuminate\Foundation\Auth\User::class))
            ->put('media/'.$model->id, [
                'title' => 'test'
            ]);

        $response->assertStatus(200);

        $this->assertThat($model::find($model->id)->title, $this->equalTo('test'));
    }

    public function testDelete()
    {
        $this->be(app(\Illuminate\Foundation\Auth\User::class));

        $model = app(MediaService::class)
            ->resource(UploadedFile::fake()->image('test.jpg', 10, 10))
            ->save();

        $response = $this
            ->actingAs(app(\Illuminate\Foundation\Auth\User::class))
            ->delete('media/'.$model->id);

        $response->assertStatus(200);

        $this->assertNull($model::find($model->id));
    }


}
