<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Collection;
use Objectivehtml\Media\Filters\Filters;
use Objectivehtml\Media\Contracts\Filter as FilterInterface;
use Objectivehtml\Media\Contracts\Filterable as FilterableInterface;
use Objectivehtml\Media\Contracts\StreamableResource as StreamableResourceInterface;

trait Filterable {

    protected $filters;

    public function getFilters(): Filters
    {
        return $this->filters ?: $this->filters = new Filters;
    }

    public function setFilters(Filters $filters): Filters
    {
        return $this->filters = $filters;
    }

    public function filters(array $filters = null)
    {
        if(is_null($filters)) {
            return $this->getFilters();
        }

        foreach($filters as $filter) {
            if($filter instanceof FilterInterface) {
                $this->filter($filter);
            }
            else if(is_array($filter)) {
                $this->filter($filter[0], ...(isset($filter[1]) ? $filter[1] : []));
            }
        }

        return $this;
    }

    public function filter($filter, ...$args): FilterableInterface
    {
        if(!$this->filters) {
            $this->filters = new Filters;
        }

        if(!$filter instanceof FilterInterface) {
            $filter = $filter::make(...$args);
        }

        if($filter->doesApply($this->mime(), $this->extension())) {
            $this->filters->push($filter);
        }

        return $this;
    }

}
