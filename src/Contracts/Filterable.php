<?php

namespace Objectivehtml\Media\Contracts;

use Illuminate\Support\Collection;
use Objectivehtml\Media\Contracts\Filterable as FilterableInterface;

interface Filterable {

    public function getFilters(): Collection;

    public function setFilters(Collection $filters): Collection;

    public function filters(array $filters = null);

    public function filter($filter, ...$args): FilterableInterface;

}
