<?php

namespace Objectivehtml\MediaManager\Strategies;

use Illuminate\Database\Eloquent\Model;

class FilenameStrategy extends Strategy
{
    public function generate(Model $model): ?string
    {
        return str_random(32) . ($model->extension ? '.' . $model->extension : null);
    }

}
