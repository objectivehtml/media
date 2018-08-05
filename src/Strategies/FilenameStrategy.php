<?php

namespace Objectivehtml\Media\Strategies;

use Objectivehtml\Media\Model;

class FilenameStrategy extends Strategy
{
    public function run(Model $model): ?string
    {
        return str_random(32) . ($model->extension ? '.' . $model->extension : null);
    }

}
