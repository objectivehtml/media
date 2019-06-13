<?php

namespace Tests\Unit;

use Media;
use Tests\TestCase;
use Objectivehtml\Media\Strategies\ObfuscatedDirectoryStrategy;
use Objectivehtml\Media\Contracts\Strategy as StrategyInterface;

class DirectoryStrategyTest extends TestCase
{

    public function testDirectoryStrategy()
    {
        $model = Media::model([
            'size' => 1028
        ]);

        $model->save();

        $this->assertTrue(Media::directoryStrategy() instanceof StrategyInterface);
        $this->assertThat(Media::directoryStrategy()($model), $this->equalTo((string) $model->getKey()));
    }

    public function testObfuscatedDirectoryStrategy()
    {
        $model = Media::model([
            'size' => 1028
        ]);

        $model->save();

        $this->assertThat(strlen(ObfuscatedDirectoryStrategy::make()($model)), $this->equalTo(32));
    }

}
