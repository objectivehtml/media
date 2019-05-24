<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Collection;

trait Metable {

    protected $meta;

    public function meta($key = null, $value = null)
    {
        $meta = $this->getMeta();

        if(is_null($key)) {
            return $meta;
        }
        else if(is_array($key)) {
            foreach($key as $index => $value) {
                $meta->put($index, $value);
            }
        }
        else if(is_null($value)) {
            return $meta->get($key);
        }
        else {
            $meta->put($key, $value);
        }

        return $this;
    }

    public function getMeta(): Collection
    {
        return $this->meta ?: $this->meta = collect();
    }

    public function setMeta(Collection $meta)
    {
        $this->meta = $meta;
    }

}
