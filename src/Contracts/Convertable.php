<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Conversions\Conversions;
use Objectivehtml\Media\Contracts\Convertable as ConvertableInterface;

interface Convertable {

    public function getConversions(): Conversions;

    public function setConversions(Conversions $conversions): Conversions;

    public function conversions(array $conversions = null);

    public function conversion($conversion, ...$args): ConvertableInterface;

    public function convert(Model $parent, array $arguments = null): Model;

}
