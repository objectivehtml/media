<?php

namespace Tests\Unit;

use Media;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Filesystem\Factory;

class StaticMediaMethodsTest extends TestCase
{
    
    public function testConfig()
    {
        $this->assertThat(Media::config(), $this->equalTo(config('media')));
        $this->assertThat(Media::config('test'), $this->equalTo(null));
        $this->assertThat(Media::config('test', 123), $this->equalTo(123));
    }
    
    public function testStorage()
    {
        $this->assertInstanceOf(Factory::class, Media::storage());
    }
    
    public function testModel()
    {
        $this->assertInstanceOf(Media::config('model'), Media::model());
    }
    
    public function testPlugins()
    {
        $model = Media::model([
            'size' => 1000,
            'filename' => 'test.jpeg'
        ]);
        
        $this->assertInstanceOf(Collection::class, Media::plugins());
        $this->assertInstanceOf(Collection::class, Media::jobs($model));
    }
                    
    public function testDirectory()
    {
        $model = Media::save([
            'size' => 1000,
            'filename' => 'test.jpeg'
        ]);
        
        $this->assertThat(Media::directory($model), $this->equalTo('1'));
    }
                
    public function testRelativePath()
    {
        $model = Media::model([
            'size' => 1000,
            'filename' => 'test.jpeg'
        ]);

        $model->save();
        
        $this->assertThat(Media::relativePath($model), $this->equalTo('1/test.jpeg'));
    }
                    
    public function testGenerateFilename()
    {
        $model = Media::save([
            'size' => 1000,
            'filename' => 'test'
        ]);
        
        $this->assertThat(strlen(Media::filename($model)), $this->equalTo(32));
        
        $model = Media::save([
            'size' => 1000,
            'filename' => 'test.jpeg'
        ]);
        
        $this->assertThat(strlen(Media::filename($model)), $this->equalTo(37));
    }
        
}
                        