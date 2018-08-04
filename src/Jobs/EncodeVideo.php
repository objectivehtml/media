<?php

namespace Objectivehtml\Media\Jobs;

use Objectivehtml\Media\Model;

class EncodeVideo extends CopyAndEncodeVideo
{
    protected function model(): Model
    {
        return $this->model;
    }
}
