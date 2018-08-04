<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;

interface Conversion {

    public function apply(Model $model);

    public function toArray(): array;

}
