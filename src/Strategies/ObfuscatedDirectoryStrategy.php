<?php

namespace Objectivehtml\Media\Strategies;

use Objectivehtml\Media\Model;

class ObfuscatedDirectoryStrategy extends DirectoryStrategy
{
    public function run(Model $model): string
    {
        return md5(implode(' ', [
            get_class($model), $model->created_at, $model->getKey()
        ]));
    }
}
