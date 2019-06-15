<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\TemporaryFile;
use Objectivehtml\Media\TemporaryModel;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Services\ImageService;

class TemporaryFileTest extends TestCase
{
    public function testTemporaryFileIsDeletedAfterException()
    {
        $temp = null;

        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $original = app(MediaService::class)->resource($file)->disk('s3')->save();
       
        try {
            TemporaryFile::make($original, function($model) use (&$temp, $original) {
                $temp = $model;

                throw new Exception('test');
            });
        }
        catch(\Exception $e) {
            $this->assertThat($e->getMessage(), $this->equalTo('test'));
        }
        
        $this->assertFalse($temp->fileExists);
        $this->assertTrue($original->fileExists);
    }

    public function testModelResourceIsReplacedByTemporaryResource()
    {
        $temp = null;

        $file = UploadedFile::fake()->image('testasdasd.jpg', 100, 100);

        $original = app(MediaService::class)->resource($file)->disk('s3')->save();
    
        TemporaryFile::make($original, function($model) use (&$temp, $original) {
            $image = app(ImageService::class)->make($model->path);

            $this->assertThat($image->width(), $this->equalTo(100));
            $this->assertThat($image->height(), $this->equalTo(100));

            $image->resize(1, 1);
            $image->save();

            $model->resource(app(MediaService::class)->resource($image));
            $model->save();

            $this->assertThat($model->resource()->width(), $this->equalTo(1));
            $this->assertThat($model->resource()->height(), $this->equalTo(1));
        });
    }
    
    public function testCreatingFromUploadedFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $original = app(MediaService::class)->resource($file)->save();

        $this->assertTrue($original->exists);

        TemporaryFile::make($original, function($model) use (&$temp) {
            $temp = $model;

            $this->assertNotNull($model->parent);
            $this->assertTrue($model->fileExists);
            $this->assertCount(1, TemporaryModel::query()->temporary()->get());
            $this->assertThat($model->context, $this->equalTo(app(MediaService::class)->config('temp.context')));

            TemporaryFile::make($model, function($model) {
                $this->assertTrue($model->fileExists);
                $this->assertCount(2, TemporaryModel::query()->temporary()->get());

                TemporaryFile::make($model, function($model) {
                    $this->assertTrue($model->fileExists);
                    $this->assertCount(3, TemporaryModel::query()->temporary()->get());
                });
            });
        });

        try {
            TemporaryFile::make($original, function($temp) {
                throw new Exception;
            });
        }
        catch(Exception $e) {
            //
        }

        TemporaryFile::make($original, function($temp) {
            $this->assertTrue($temp->fileExists);
            $this->assertCount(1, TemporaryModel::query()->temporary()->get());
        });

        $this->assertFalse($temp->exists);
        $this->assertInternalType('string', $temp->path);
        $this->assertFalse(file_exists($temp->path));
        $this->assertCount(0, TemporaryModel::query()->temporary()->get());
    }
}
