<?php

namespace Objectivehtml\Media\Resources;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Testing\File as FakeFile;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Exceptions\InvalidResourceException;

class FileResource extends StreamableResource {

    public function __construct(File $resource)
    {
        parent::__construct($resource);
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
        return $this->resource instanceof UploadedFile ? $this->resource->getClientOriginalName() : $this->resource->getFilename();
    }

    public function stream()
    {
        if($this->resource instanceof FakeFile) {
            return $this->resource->tempFile;
        }

        if(file_exists($path = $this->resource->getPathname())) {
            return fopen($path, 'rb');
        }

        throw new InvalidResourceException();
    }

}
