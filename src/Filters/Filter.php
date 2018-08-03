<?php

namespace Objectivehtml\MediaManager\Filters;

use Objectivehtml\MediaManager\Media;
use Illuminate\Contracts\Support\Arrayable;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Support\ArrayableFactory;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;
use Objectivehtml\MediaManager\Contracts\Applyable as ApplyableInterface;

abstract class Filter implements Arrayable, ApplyableInterface, FilterInterface {

    use Applyable, ArrayableFactory;

}
