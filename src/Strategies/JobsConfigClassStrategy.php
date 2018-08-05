<?php

namespace Objectivehtml\Media\Strategies;

use InvalidArgumentException;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class JobsConfigClassStrategy
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function __invoke($class)
    {
        return $this->run($class);
    }

    public function run($class)
    {
        if(isset($class[1])) {
            array_splice($class[1], 0, 0, [
                $this->model
            ]);
        }

        return ConfigClassStrategy::make()($class);
    }

    public static function make(Model $model)
    {
        return new static($model);
    }

}
