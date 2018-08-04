<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;

interface Strategy
{

    public function __invoke(Model $model): ?string;

    public function generate(Model $model): ?string;

    public static function make();

}
