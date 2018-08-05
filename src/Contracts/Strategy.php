<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;

interface Strategy
{
    public function __invoke(Model $model);

    public function run(Model $model);

    public static function make();
}
