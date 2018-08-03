<?php

namespace Objectivehtml\MediaManager\Contracts;

use Objectivehtml\MediaManager\Model;

interface Applyable {

    public function doesApply($mime, $extension): bool;

    public function doesApplyToModel(Model $model): bool;

    public function doesMatchMime($mime): bool;

    public function doesMatchExtension($extension): bool;

    public function applyToMimes(): array;

    public function applyToExtensions(): array;

}
