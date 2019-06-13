<?php

namespace Tests\Unit\Support;

use Media;
use Tests\TestCase;
use Illuminate\Support\Collection;

class MetableTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $model = Media::model();
        
        $this->assertInstanceOf(Collection::class, $model->meta);
        $this->assertCount(0, $model->meta);

        $model->meta = [
            'a' => 1
        ];

        $this->assertThat($model->meta->get('a'), $this->equalTo(1));

        $model->meta('a', null);
        $model->meta('b', 2);
        $model->meta([
            'c' => 3,
            'd' => 4,
            'e' => null,
            'f' => null
        ]);

        $model->tag(1, 2, 3);
        $model->save();
        $model->tag('image');

        $this->assertCount(4, $model->tags);
        $this->assertArrayHasKey('b', $model->toArray()['meta']);
        $this->assertArrayHasKey('c', $model->toArray()['meta']);
        $this->assertArrayHasKey('d', $model->toArray()['meta']);
    }
}
