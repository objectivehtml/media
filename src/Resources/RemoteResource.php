<?php

namespace Objectivehtml\Media\Resources;

use Mimey\MimeTypes;
use Illuminate\Http\Testing\File as FakeFile;
use Symfony\Component\HttpFoundation\File\File;
use Objectivehtml\Media\Exceptions\InvalidResourceException;

class RemoteResource extends StreamableResource {

    public function __construct(string $resource)
    {
        parent::__construct($resource);
    }

    public function mime(): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->buffer($this->resource);
    }

    public function extension(): ?string
    {
        return (new MimeTypes)->getExtension($this->mime());
    }

    public function size(): int
    {
        return $this->resource->getSize();
    }

    public function filename(): string
    {
        return $this->resource->getFilename();
    }

    public function originalFilename(): ?string
    {
        return null;
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
