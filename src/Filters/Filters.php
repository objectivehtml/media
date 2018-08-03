<?php

namespace Objectivehtml\MediaManager\Filters;

use Illuminate\Support\Collection;
use Objectivehtml\MediaManager\Contracts\Filter as FilterInterface;

class Filters extends Collection {

    public function __construct(array $items = null)
    {
        parent::__construct();

        if($items) {
            foreach($items as $item) {
                if($item instanceof FilterInterface) {
                    $this->push($item);
                }
                else if(is_array($item)) {
                    $this->push($item[0]::make(...(isset($item[1]) ? $item[1] : [])));
                }
            }
        }
    }

    public function apply($to)
    {
        dd('apply filter to', $to);
    }

}
