<?php

namespace Objectivehtml\MediaManager\Contracts;

use Objectivehtml\MediaManager\Model;

interface Conversion {

    public function apply(Model $model);

    public function toArray(): array;

}
