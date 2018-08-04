<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Filters\Filters;
use Objectivehtml\Media\Contracts\Filterable as FilterableInterface;

interface Filterable {

    public function getFilters(): Filters;

    public function setFilters(Filters $filters): Filters;

    public function filters(array $filters = null);

    public function filter($filter, ...$args): FilterableInterface;

}
