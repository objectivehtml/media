<?php

namespace Objectivehtml\Media\Support;

use ReflectionClass;

trait ArrayableFactory {

    public function toArray(): array
    {
        foreach((new ReflectionClass($this))->getProperties() as $property) {
            $arguments[] = $property->getValue($this);
        }

        return array_merge([static::class], isset($arguments) ? [$arguments] : []);
    }

    public static function make(...$args)
    {
        return new static(...$args);
    }

}
