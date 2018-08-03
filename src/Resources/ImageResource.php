<?php

namespace Objectivehtml\MediaManager\Resources;

use Intervention\Image\Image;
use Objectivehtml\MediaManager\Model;
use Illuminate\Http\Testing\File as FakeFile;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\MediaManager\Exceptions\InvalidResourceException;

class ImageResource extends StreamableResource {

    public function __construct(Image $resource)
    {
        $this->resource = $resource;
    }

    public function mime(): string
    {
        return $this->resource->mime();
    }

    public function extension(): ?string
    {
        return null;
    }

    public function size(): int
    {
        return $this->resource->filesize();
    }

    public function filename(): string
    {
        return null;
    }

    public function originalFilename(): ?string
    {
        return null;
    }

    public function getResource()
    {
        return $this->resource->encode();
    }

    public function stream()
    {
        return $this->resource->encoded();
    }

}
