<?php

namespace Objectivehtml\MediaManager\Jobs;

use Objectivehtml\MediaManager\Model;

class EncodeVideo extends CopyAndEncodeVideo
{
    protected function model(): Model
    {
        return $this->model;
    }
}
