<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Objectivehtml\Media\Contracts\Convertable as ConvertableInterface;

interface Convertable {

    public function getConversions(): Collection;

    public function setConversions(Collection $conversions): Collection;

    public function conversions(array $conversions = null);

    public function conversion($conversion, ...$args): ConvertableInterface;

    public function convert(Model $parent, array $arguments = null): Model;

}
