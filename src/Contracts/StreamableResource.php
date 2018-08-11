<?php

namespace Objectivehtml\Media\Contracts;

use Objectivehtml\Media\Model;
use Illuminate\Contracts\Filesystem\Factory;

interface StreamableResource {

    public function mime(): ?string;

    public function extension(): ?string;

    public function size(): int;

    public function filename(): ?string;

    public function originalFilename(): ?string;

    public function getResource();

    public function setResource($resource);

    public function storage(): Factory;

    public function model(array $attributes = []): Model;

    public function save(array $attributes = []): Model;

    public function stream();

}
