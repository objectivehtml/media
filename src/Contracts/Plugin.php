<?php

namespace Objectivehtml\MediaManager\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Plugin {

    public function jobs(Model $model): array;

    public function filters(Model $model): array;

    public function conversions(Model $model): array;

    public function generators(Model $model): array;

    public function doesMeetRequirements(): bool;

}
