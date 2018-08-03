<?php

namespace Objectivehtml\MediaManager\Conversions;

use Objectivehtml\MediaManager\Media;
use Illuminate\Contracts\Support\Arrayable;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Support\ArrayableFactory;
use Objectivehtml\MediaManager\Contracts\Applyable as ApplyableInterface;
use Objectivehtml\MediaManager\Contracts\Conversion as ConversionInterface;

abstract class Conversion implements Arrayable, ApplyableInterface, ConversionInterface {

    use Applyable, ArrayableFactory;

}
