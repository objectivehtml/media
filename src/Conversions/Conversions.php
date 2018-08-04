<?php

namespace Objectivehtml\Media\Conversions;

use Illuminate\Support\Collection;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;

class Conversions extends Collection {

    public function __construct(array $items = null)
    {
        parent::__construct();

        if($items) {
            foreach($items as $item) {
                if($item instanceof ConversionInterface) {
                    $this->push($item);
                }
                else if(is_array($item)) {
                    $this->push($item[0]::make(...(isset($item[1]) ? $item[1] : [])));
                }
            }
        }
    }

}
