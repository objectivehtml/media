<?php

namespace Objectivehtml\MediaManager\Strategies;

use Illuminate\Database\Eloquent\Model;
use Objectivehtml\MediaManager\Contracts\Strategy as StrategyInterface;

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
