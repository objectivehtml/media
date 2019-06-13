<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;

interface Plugin {

    public function jobs(Model $model): array;

    public function filters(Model $model): array;

    public function conversions(Model $model): array;

    public function doesMeetRequirements(): bool;
}
