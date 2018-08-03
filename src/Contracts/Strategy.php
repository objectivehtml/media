<?php

namespace Objectivehtml\MediaManager\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Strategy
{

    public function __invoke(Model $model): ?string;

    public function generate(Model $model): ?string;

    public static function make();

}
