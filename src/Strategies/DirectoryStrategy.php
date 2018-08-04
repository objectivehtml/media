<?php

namespace Objectivehtml\Media\Strategies;

use Objectivehtml\Media\Model;

class DirectoryStrategy extends Strategy
{
    public function generate(Model $model): ?string
    {
        return $model->parent ? $model->parent->getKey() : $model->getKey();
    }

}
