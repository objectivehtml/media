<?php

namespace Tests\Unit\Resources;

use Tests\TestCase;
use FFMpeg\Media\Video;
use Objectivehtml\Media\Model;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFProbe\DataMapping\Format;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Services\VideoService;
use FFMpeg\FFProbe\DataMapping\StreamCollection;

class VideoServiceTest extends TestCase
{
    
    public function testInstantiatingFFMPEGTest()
    {
        $this->assertInstanceOf(\FFMpeg\FFMpeg::class, app(VideoService::class)->ffmpeg());
    }

    public function testInstantiatingFFProbeTest()
    {
        $this->assertInstanceOf(\FFMpeg\FFProbe::class, app(VideoService::class)->ffprobe());
    }

    public function testHeightTest()
    {
        $this->assertEquals(1080, app(VideoService::class)->height(__DIR__.'/../../src/video.mp4'));
    }

    public function testWidthRatioTest()
    {
        $this->assertEquals(1920, app(VideoService::class)->width(__DIR__.'/../../src/video.mp4'));
    }

    public function testAspectRatioTest()
    {
        $dimensions = app(VideoService::class)->dimensions(__DIR__.'/../../src/video.mp4');
        
        $this->assertInstanceOf(Dimension::class, $dimensions);

        $this->assertEquals('16:9', app(VideoService::class)->aspectRatio($dimensions->getWidth(), $dimensions->getHeight()));
    }

    public function testBitRateTest()
    {
        $this->assertEquals(3385815, app(VideoService::class)->bitRate(__DIR__.'/../../src/video.mp4'));
    }

    public function testDurationTest()
    {
        $this->assertEquals(13.56, app(VideoService::class)->duration(__DIR__.'/../../src/video.mp4'));
    }

    public function testFormatTest()
    {
        $this->assertInstanceOf(Format::class, app(VideoService::class)->format(__DIR__.'/../../src/video.mp4'));
    }

    public function testStreamsTest()
    {
        $this->assertInstanceOf(StreamCollection::class, app(VideoService::class)->streams(__DIR__.'/../../src/video.mp4'));
    }

    public function testVideosTest()
    {
        $this->assertInstanceOf(StreamCollection::class, app(VideoService::class)->videos(__DIR__.'/../../src/video.mp4'));
    }

    public function testOpenTest()
    {
        $this->assertInstanceOf(Video::class, app(VideoService::class)->open(__DIR__.'/../../src/video.mp4'));
    }

    public function testExtractFrameFromModelTest()
    {
        $model = app(MediaService::class)
            ->resource(__DIR__.'/../../src/video.mp4')
            ->save();
    
        $this->assertInstanceOf(Model::class, $frame = app(VideoService::class)->extractFrame($model));
        $this->assertInstanceOf(Model::class, $frame->parent);
    }

    public function testExtractFrameFromPathTest()
    {
        $this->assertInstanceOf(Model::class, app(VideoService::class)->extractFrame(__DIR__.'/../../src/video.mp4'));
    }
}
