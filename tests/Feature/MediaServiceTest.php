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

class MediaServiceTest extends TestCase
{

    public function testResourceFromFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)->resource($file);

        try {
            $this->assertInstanceOf(\Intervention\Image\Image::class, Image::make($resource->stream()));
        }
        catch(Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSavingResource()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 10, 10);

        $model = app(MediaService::class)
            ->resource($file)
            ->preserveOriginal(true)
            ->filter(Greyscale::class)
            ->filter(Crop::class, 100, 100)
            ->filter(Crop::class, 100, 100)
            ->conversion(Thumbnail::class, 100, 100)
            ->tag('image')
            ->tags(['images'])
            ->meta([
                'custom_key' => 'some value'
            ])
            ->save();

        $model = $model::find($model->id);

        $this->assertNotNull($model->directory);
        $this->assertCount(3, $model->filters);
        $this->assertCount(1, $model->conversions);
        $this->assertCount(2, $model->tags);

        $this->assertArrayHasKey('custom_key', $model->meta->toArray());
        $this->assertGreaterThan(0, $model->children->count());
        //$this->assertGreaterThan(0, $model->meta->get('colors'));

        $model->children->each(function($child) {
            app(MediaService::class)->storage()->disk($child->disk)->assertExists($child->relative_path);
        });
    }

}
