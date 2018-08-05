<?php

namespace Objectivehtml\Media\Strategies;

use InvalidArgumentException;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class ConfigClassStrategy
{
    public function __invoke($class)
    {
        return $this->run($class);
    }

    public function run($class)
    {
        if(is_object($class)) {
            return $class;
        }
        else if(is_string($class)) {
            return new $class;
        }
        else if(is_array($class)) {
            return new $class[0](...(isset($class[1]) ? $class[1] : []));
        }

        throw new InvalidArgumentException;
    }

    public static function make()
    {
        return new static();
    }

}
