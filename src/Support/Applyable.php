<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\Model;

trait Applyable {

    public function doesApply($mime, $extension): bool
    {
        return $this->doesMatchMime($mime) || $this->doesMatchExtension($extension);
    }

    public function doesApplyToModel(Model $model): bool
    {
        return $this->doesApply($model->mime, $model->extension);
    }

    public function doesMatchMime($mime): bool
    {
        return in_array($mime, $this->applyToMimes());
    }

    public function doesMatchExtension($extension): bool
    {
        return in_array($extension, $this->applyToExtensions());
    }


}
