<?php

namespace Objectivehtml\MediaManager\Support;

use Illuminate\Support\Collection;
use Objectivehtml\MediaManager\Contracts\Taggable as TaggableInterface;

trait Taggable {

    protected $tags;

    public function tag($key): TaggableInterface
    {
        $this->tags([$key]);

        return $this;
    }

    public function tags(array $keys = null)
    {
        if(is_null($keys)) {
            return $this->getTags();
        }

        if(!$this->tags) {
            $this->tags = collect($keys);
        }
        else {
            $this->tags = $this->tags->merge($keys);
        }

        return $this;
    }

    public function forgetTag($key): TaggableInterface
    {
        $this->tags->forget($key);

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags ?: $this->tags = collect();
    }

    public function setTags(Collection $tags)
    {
        $this->tags = $tags;
    }

}
