<?php

namespace Tests\Unit\Plugins;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Services\MediaService;
use Objectivehtml\Media\Resources\FileResource;

class GeocoderPluginTest extends TestCase
{
    public function testGeocoderReturnsLocation()
    {   
        $model = app(MediaService::class)
            ->resource(__DIR__.'/../../src/image.jpeg')
            ->save();

        $this->assertNotNull($model->meta->get('geocoder'));
    }

    /*
    public function testGeocodeSynchronously()
    {   
        $model = app(MediaService::class)
            ->resource(__DIR__.'/../../src/image.jpeg')
            ->queue('sync')
            ->save();

        $this->assertNotNull($model->meta->get('geocoder'));
    }
    */
    
}
