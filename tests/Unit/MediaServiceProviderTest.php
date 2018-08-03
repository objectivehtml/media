<?php

namespace Tests\Unit;

use Media;
use Tests\TestCase;
use Objectivehtml\MediaManager\MediaService;
use Objectivehtml\MediaManager\MediaServiceProvider;
use Objectivehtml\MediaManager\Facades\Media as Facade;

class MediaServiceProviderTest extends TestCase
{

    public function testServiceCanBoot()
    {
        try {
            $this->assertTrue(app(MediaService::class) instanceof MediaService);
        }
        catch(Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testFacadeCanBoot()
    {
        try {
            $this->assertTrue(app('Media') instanceof Facade);
        }
        catch(Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testHasDefaultConfig()
    {
        $this->assertTrue(is_array(Media::config()));
    }

    public function testSetConfig()
    {
        $this->assertThat(Media::config('test'), $this->equalTo(null));

        Media::config('test', 123);

        $this->assertThat(Media::config('test'), $this->equalTo(123));
    }

}
