<?php

namespace Objectivehtml\MediaManager\Support;

use Illuminate\Support\Collection;

trait Metable {

    protected $meta;

    public function meta($key = null, $value = null)
    {
        if(!$this->meta) {
            $this->meta = collect();
        }

        if(is_null($key)) {
            return $this->meta;
        }
        else if(is_array($key)) {
            foreach($key as $index => $value) {
                $this->meta->put($index, $value);
            }
        }
        else if(is_null($value)) {
            return $this->meta->get($key);
        }
        else {
            $this->meta->put($key, $value);
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
