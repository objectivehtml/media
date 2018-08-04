<?php

namespace Objectivehtml\Media\Contracts;

use Illuminate\Support\Collection;

interface Metable {

    public function meta($key = null, $value = null);

    public function getMeta(): Collection;

    public function setMeta(Collection $meta);

}
