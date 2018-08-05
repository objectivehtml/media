<?php

namespace Objectivehtml\Media\Strategies;

use Objectivehtml\Media\Model;

class ModelMatchingStrategy extends Strategy
{
    public function run(Model $model): ?Model
    {
        return $model::query()
            ->whereSize($model->size)
            ->whereOrigFilename($model->orig_filename)
            ->whereNotNull('orig_filename')
            ->first();
    }
}
