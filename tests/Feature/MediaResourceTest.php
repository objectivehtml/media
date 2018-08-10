<?php

namespace Tests\Feature;

use Media;
use Storage;
use Exception;
use Tests\TestCase;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Filters\Image\Crop;
use Objectivehtml\Media\Filters\Image\Greyscale;
use Objectivehtml\Media\Conversions\Audio\Waveform;
use Objectivehtml\Media\Conversions\Video\Homepage;

class MediaResourceTest extends TestCase
{

    public function testResourceOptions()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)->resource($file);

        $this->assertTrue($resource->preserveOriginal());

        $resource->preserveOriginal(false);

        $this->assertFalse($resource->preserveOriginal());

        $this->assertNull($resource->thisKeyDoesNotExist());

        $resource->thisKeyDoesNotExist('test');

        $this->assertThat($resource->thisKeyDoesNotExist(), $this->equalTo('test'));
    }

    public function testAddingFiltersToResourceFromFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)
            ->resource($file)
            ->preserveOriginal(true)
            ->filter(new Greyscale)
            ->filters([
                [Crop::class, [100, 100]]
            ]);

        $this->assertThat($resource->filters()->count(), $this->equalTo(2));

        $resource->filters()->forget(0);
        $resource->filters()->forget(1);

        $this->assertThat($resource->filters()->count(), $this->equalTo(0));
    }

    public function testAddingTagsToResourceFromFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)
            ->resource($file)
            ->tags(['image', 'images'])
            ->tag('large');

        $this->assertThat($resource->tags()->count(), $this->equalTo(3));

        $resource->tags()->forget(0);
        $resource->tags()->forget(1);
        $resource->tags()->forget(2);

        $this->assertThat($resource->tags()->count(), $this->equalTo(0));
    }

    public function testAddingMetaToResourceFromFile()
    {
        $file = UploadedFile::fake()->image('test.jpg', 10, 10);

        $resource = app(MediaService::class)
            ->resource($file)
            ->meta([
                'a' => 1,
                'b' => 2
            ])
            ->meta('c', 3);

        $this->assertThat($resource->meta()->count(), $this->equalTo(3));

        $resource->tags()->forget(0);
        $resource->tags()->forget(1);
        $resource->tags()->forget(2);

        $this->assertThat($resource->tags()->count(), $this->equalTo(0));
    }

    public function testAddingConversionsToResourceFromFile()
    {
        $file = UploadedFile::fake()->create('test.aac');

        $resource = app(MediaService::class)
            ->resource($file)
            ->conversion(Waveform::class)
            ->conversions([
                [Waveform::class],
                [Waveform::class]
            ]);

        $this->assertThat($resource->conversions()->count(), $this->equalTo(3));

        $resource->conversions()->forget(0);
        $resource->conversions()->forget(1);
        $resource->conversions()->forget(2);

        $this->assertThat($resource->conversions()->count(), $this->equalTo(0));
    }

    public function testResourceFromString()
    {
        $model = app(MediaService::class)
            ->resource(__dir__ . '/../src/guitar.m4a')
            ->directory('test')
            ->save([
                'filename' => 'test.m4a'
            ]);

        $this->assertTrue($model->fileExists);
        $this->assertNotNull($model = $model->children()->context('waveform')->first());
        $this->assertTrue($model->fileExists);
    }

    public function testAudioResourceFromFile()
    {
        $file = new File(__dir__ . '/../src/guitar.m4a');

        $model = app(MediaService::class)
            ->resource($file)
            ->save([
                'directory' => 'test',
                'filename' => 'test.m4a'
            ]);

        $this->assertThat($model->size, $this->equalTo($file->getSize()));
        $this->assertTrue($model->fileExists);
        $this->assertNotNull($model = $model->children()->context('waveform')->first());
        $this->assertTrue($model->fileExists);
    }

    public function testPreventDuplicates()
    {
        $file1 = UploadedFile::fake()->image('test.jpg', 10, 10);

        $model = app(MediaService::class)->resource($file1)->save();

        $this->assertThat($model->id, $this->equalTo(1));

        $model2 = app(MediaService::class)->resource($file1)->model(null, true);

        $this->assertThat($model->id, $this->equalTo(1));

        $file2 = UploadedFile::fake()->image('test2.jpg', 10, 10);

        $model = app(MediaService::class)->resource($file2)->save();

        $this->assertFalse($model->id === 1);
    }

    public function testVideoResourceFromFile()
    {
        $file = new File(__dir__ . '/../src/video.mp4');

        $model = app(MediaService::class)
            ->resource($file)
            ->save([
                'filename' => 'test.mp4'
            ]);

        $this->assertTrue($model->fileExists);
        $this->assertCount(5, $model->children);
    }

    public function testHomepageVideoConversion()
    {
        $file = new File(__dir__ . '/../src/video.mp4');

        $model = app(MediaService::class)
            ->resource($file)
            ->conversion(new Homepage)
            ->save([
                'filename' => 'test.mp4'
            ]);

        $this->assertTrue($model->fileExists);
        $this->assertCount(6, $model->children);
    }

}
