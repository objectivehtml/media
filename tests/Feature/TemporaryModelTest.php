<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\TemporaryFile;
use Objectivehtml\Media\TemporaryModel;

class TemporaryModelTest extends TestCase
{

    public function testCreatingFromUploadedFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $model = app(MediaService::class)
            ->resource($file)
            ->save();

        $this->assertTrue($model->exists);

        $temp = null;

        TemporaryFile::make($model, function($model) use (&$temp) {
            $temp = $model;
            $this->assertTrue($model->fileExists);
            $this->assertCount(1, TemporaryModel::query()->temporary()->get());

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
            TemporaryFile::make($model, function($temp) {
                throw new Exception;
            });
        }
        catch(Exception $e) {
            //
        }

        TemporaryFile::make($model, function($temp) {
            $this->assertTrue($temp->fileExists);
            $this->assertCount(1, TemporaryModel::query()->temporary()->get());
        });

        $this->assertFalse($temp->exists);
        $this->assertInternalType('string', $temp->path);
        $this->assertFalse(file_exists($temp->path));
        $this->assertCount(0, TemporaryModel::query()->temporary()->get());
    }

}
