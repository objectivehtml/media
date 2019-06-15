<?php

namespace Objectivehtml\Media\Strategies;

use InvalidArgumentException;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class ConfigClassStrategy
{
    protected $args = [];

    public function __construct(...$args)
    {
        $this->args = $args;
    }

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
            return new $class(...$this->args);
        }
        else if(is_array($class) && isset($class[0])) {
            return new $class[0](...array_merge($this->args, isset($class[1]) ? $class[1] : []));
        }

        throw new InvalidArgumentException;
    }

    public static function make(...$args)
    {
        return new static(...$args);
    }

    public static function map(Array $array, ...$args)
    {
        return array_map(static::make(...$args), $array);
    }

    public static function collect(Array $array, ...$args)
    {
        return collect(static::map($array, ...$args));
    }

}
