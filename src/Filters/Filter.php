<?php

namespace Objectivehtml\Media\Filters;

use Objectivehtml\Media\Media;
use Illuminate\Contracts\Support\Arrayable;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ArrayableFactory;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;
use Objectivehtml\Media\Contracts\Applyable as ApplyableInterface;

abstract class Filter implements Arrayable, ApplyableInterface, FilterInterface {

    use Applyable, ArrayableFactory;

}
