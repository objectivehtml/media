<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\TemporaryFile;
use Objectivehtml\Media\TemporaryModel;

class TemporaryFileTest extends TestCase
{

    public function testCreatingFromUploadedFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $original = app(MediaService::class)
            ->resource($file)
            ->save();

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
