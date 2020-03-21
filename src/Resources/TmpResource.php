<?php

namespace Objectivehtml\Media\Resources;

use InvalidArgumentException;
use Mimey\MimeTypes;

class TmpResource extends StreamableResource {

    public function __construct($resource)
    {
        if(!is_resource($resource)) {
            throw new InvalidArgumentException(
                'The first parameter must be a tmpfile stream.'
            );
        }

        parent::__construct($resource);
    }

    public function __destruct()
    {
        if(is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    public function mime(): string
    {
        return mime_content_type($this->resource);
    }

    public function extension(): ?string
    {
        return (new MimeTypes)->getExtension($this->mime());
    }

    public function size(): int
    {
        return fstat($this->resource)['size'];
    }

    public function filename(): string
    {
        return basename(stream_get_meta_data($this->resource)['uri']);
    }

    public function originalFilename(): string
    {
        return $this->filename();
    }

    public function stream()
    {
        return $this->resource;
    }
}
