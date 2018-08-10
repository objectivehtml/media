<?php

namespace Tests\Feature;

use PDOException;
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

        dispatch(new ResizeMaxDimensions($model, $maxWidth = 50, $maxHeight = 50));

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

}
