<?php

namespace Objectivehtml\MediaManager\Strategies;

use Illuminate\Database\Eloquent\Model;

class ObfuscatedDirectoryStrategy extends DirectoryStrategy
{

    public function generate(Model $model): ?string
    {
        return md5(implode(' ', [
            get_class($model),
            $model->created_at,
            $model->getKey()
        ]));
    }

}
