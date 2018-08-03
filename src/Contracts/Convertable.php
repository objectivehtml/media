<?php

namespace Objectivehtml\MediaManager\Contracts;

use Objectivehtml\MediaManager\Model;
use Objectivehtml\MediaManager\Conversions\Conversions;
use Objectivehtml\MediaManager\Contracts\Convertable as ConvertableInterface;

interface Convertable {

    public function getConversions(): Conversions;

    public function setConversions(Conversions $conversions): Conversions;

    public function conversions(array $conversions = null);

    public function conversion($conversion, ...$args): ConvertableInterface;

    public function convert(Model $parent, array $arguments = null): Model;

}
