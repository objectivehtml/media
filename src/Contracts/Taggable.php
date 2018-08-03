<?php

namespace Objectivehtml\MediaManager\Contracts;

use Illuminate\Support\Collection;

interface Taggable {

    public function tag($key): self;

    public function tags(array $keys = null);

    public function getTags(): Collection;

    public function setTags(Collection $tags);

}
