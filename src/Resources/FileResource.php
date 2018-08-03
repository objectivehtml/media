<?php

namespace Objectivehtml\MediaManager\Resources;

use Illuminate\Http\Testing\File as FakeFile;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\MediaManager\Exceptions\InvalidResourceException;

class FileResource extends StreamableResource {

    public function __construct(File $resource)
    {
        $this->resource = $resource;
    }

    public function mime(): string
    {
        return $this->resource->getMimeType();
    }

    public function extension(): ?string
    {
        return $this->resource->guessExtension();
    }

    public function size(): int
    {
        return $this->resource->getSize();
    }

    public function filename(): string
    {
        return $this->resource->getFilename();
    }

    public function originalFilename(): string
    {
        return $this->resource instanceof File ? $this->resource->getFilename() : $this->resource->getClientOriginalName();
    }

    public function getResource()
    {
        if($this->resource instanceof FakeFile) {
            return $this->resource->tempFile;
        }
        else if($this->resource instanceof File) {
            return file_get_contents($this->resource->getPathname());
        }

        return $this->resource;
    }

    public function stream()
    {
        if($this->resource instanceof FakeFile) {
            return $this->resource->tempFile;
        }

        if(file_exists($path = $this->resource->getPath())) {
            return file_get_contents($this->resource->getPath());
        }

        throw new InvalidResourceException();
    }

}
