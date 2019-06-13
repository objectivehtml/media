<?php

namespace Objectivehtml\Media\Resources;

use Mimey\MimeTypes;
use Intervention\Image\Image;

class ImageResource extends StreamableResource {

    public function __construct(Image $resource)
    {
        parent::__construct($resource);
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->resource, $name)) {
            return $this->resource->$name(...$arguments);
        }

        return parent::__call($name, $arguments);
    }

    public function mime(): string
    {
        return $this->resource->mime();
    }

    public function extension(): ?string
    {
        return (new MimeTypes)->getExtension($this->mime());
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

    public function stream()
    {
        return $this->resource->stream($this->extension(), $this->size());
    }

}
