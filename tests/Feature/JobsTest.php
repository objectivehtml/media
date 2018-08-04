<?php

namespace Tests\Feature;

use PDOException;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Intervention\Image\ImageManagerStatic as Image;
use Objectivehtml\Media\Jobs\ApplyFilters;
use Objectivehtml\Media\Jobs\ApplyConversions;
use Objectivehtml\Media\Jobs\MoveModelToDisk;
use Objectivehtml\Media\Jobs\PreserveOriginal;
use Objectivehtml\Media\Jobs\RemoveModelFromDisk;
use Objectivehtml\Media\Jobs\ResizeMaxDimensions;
use Objectivehtml\Media\Filters\Image\Crop;
use Objectivehtml\Media\Filters\Image\Greyscale;

class JobsTest extends TestCase
{

    public function testMoveFileToDisk()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 10, 10);

        $resource = app(MediaService::class)->resource($file);

        $model = app(MediaService::class)->model([
            'disk' => $fromDisk = 'local',
            'size' => $resource->size(),
            'orig_filename' => $resource->originalFilename()
        ]);

        $model->save();

        app(MediaService::class)->storage()->disk($fromDisk)->put($model->relative_path, $resource->getResource());
        app(MediaService::class)->storage()->disk($fromDisk)->assertExists($model->relative_path);

        dispatch(new MoveModelToDisk($model, $toDisk = 'public'));

        app(MediaService::class)->storage()->disk($fromDisk)->assertMissing($model->relative_path);
        app(MediaService::class)->storage()->disk($toDisk)->assertExists($model->relative_path);
    }

    public function testResizeMaxDimensions()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 3072, 2304);

        $resource = app(MediaService::class)->resource($file);

        $model = app(MediaService::class)->model([
            'size' => $resource->size(),
            'disk' => app(MediaService::class)->config('temp.disk'),
            'orig_filename' => $resource->originalFilename()
        ]);

        $model->save();

        app(MediaService::class)->storage()->disk($model->disk)->put($model->relative_path, $resource->getResource());

        $maxWidth = app(MediaService::class)->config('image.max_width');
        $maxHeight = app(MediaService::class)->config('image.max_height');

        dispatch(new ResizeMaxDimensions($model, $maxWidth, $maxHeight));

        $image = Image::make(app(MediaService::class)->storage()->disk($model->disk)->path($model->relative_path));

        $this->assertThat($image->width(), $this->equalTo($maxWidth));
        $this->assertThat($image->height(), $this->equalTo($maxHeight));
    }

    public function testRemoveFileFromDisk()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 10, 10);

        $resource = app(MediaService::class)->resource($file);

        $model = app(MediaService::class)->model([
            'size' => $resource->size(),
            'disk' => app(MediaService::class)->config('temp.disk'),
            'orig_filename' => $resource->originalFilename()
        ]);

        $model->save();

        app(MediaService::class)->storage()->disk($model->disk)->put($model->relative_path, $resource->getResource());

        dispatch(new RemoveModelFromDisk($model));

        app(MediaService::class)->storage()->disk($model->disk)->assertMissing($model->relative_path);
    }

    public function testPreserveOriginal()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 10, 10);

        $model = app(MediaService::class)
            ->resource($file)
            ->preserveOriginal(true)
            ->save();

        app(MediaService::class)->storage()->disk($model->disk)->assertExists($model->relative_path);

        PreserveOriginal::dispatch($model);

        $this->assertThat($model->children->first()->context, $this->equalTo('original'));
        $this->assertNotEquals($model->children->first()->relative_path, $model->relative_path);

        app(MediaService::class)->storage()->disk($model->disk)->assertExists($model->relative_path);
        app(MediaService::class)->storage()->disk($model->children->first()->disk)->assertExists($model->children->first()->relative_path);
    }

    public function testApplyConversions()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 10, 10);

        $model = app(MediaService::class)
            ->resource($file)
            ->save();

        $image = Image::make($model->path);

        $this->assertCount(app(MediaService::class)->conversions($model)->count() + 1, $model->children);

        $image = Image::make($model->children->last()->path);

        $this->assertThat($model->children->last()->context, $this->equalTo('thumbnail'));
        $this->assertThat($image->width(), $this->equalTo(100));
        $this->assertThat($image->height(), $this->equalTo(100));
        $this->assertTrue($model::find($model->id)->ready);
    }

    public function testApplyFilters()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 200, 200);

        $model = app(MediaService::class)
            ->resource($file)
            ->filters([
                [Greyscale::class],
                [Crop::class, [100, 100]]
            ])
            ->save();

        $image = Image::make($model->path);

        $this->assertThat($image->width(), $this->equalTo(100));
        $this->assertThat($image->height(), $this->equalTo(100));
    }

}
