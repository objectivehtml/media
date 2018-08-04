<?php

namespace Objectivehtml\Media\Strategies;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Contracts\Strategy as StrategyInterface;

abstract class Strategy implements StrategyInterface
{
    public function __invoke(Model $model = null): ?string
    {
        return $this->generate($model);
    }

    public static function make()
    {
        return new static();
    }
}
