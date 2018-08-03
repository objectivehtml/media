<?php

namespace Objectivehtml\MediaManager\Contracts;

use Objectivehtml\MediaManager\Filters\Filters;
use Objectivehtml\MediaManager\Contracts\Filterable as FilterableInterface;

interface Filterable {

    public function getFilters(): Filters;

    public function setFilters(Filters $filters): Filters;

    public function filters(array $filters = null);

    public function filter($filter, ...$args): FilterableInterface;

}
