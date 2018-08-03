<?php

namespace Objectivehtml\MediaManager\Strategies;

use Illuminate\Database\Eloquent\Model;

class DirectoryStrategy extends Strategy
{
    public function generate(Model $model): ?string
    {
        return $model->getKey();
    }

}
