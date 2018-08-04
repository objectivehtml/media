<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;

interface Filter {

    public function apply(Model $model);

    public function toArray(): array;

}
