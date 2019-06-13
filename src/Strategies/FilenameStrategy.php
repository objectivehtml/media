<?php

namespace Objectivehtml\Media\Strategies;

use Illuminate\Support\Str;
use Objectivehtml\Media\Model;

class FilenameStrategy extends Strategy
{
    public function run(Model $model): ?string
    {
        return Str::random(32) . ($model->extension ? '.' . $model->extension : null);
    }

}
