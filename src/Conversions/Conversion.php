<?php

namespace Objectivehtml\Media\Conversions;

use Objectivehtml\Media\Media;
use Illuminate\Contracts\Support\Arrayable;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ArrayableFactory;
use Objectivehtml\Media\Contracts\Applyable as ApplyableInterface;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

abstract class Conversion implements Arrayable, ApplyableInterface, ConversionInterface {

    use Applyable, ArrayableFactory;

}
