<?php

namespace Objectivehtml\Media\Resources;

use Mimey\MimeTypes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RemoteResource extends StreamableResource {

    protected $meta;

    protected $headers;

    public function __construct($resource)
    {
        if(!is_resource($resource)) {
            $resource = fopen($resource, 'rb');
        }

        parent::__construct($resource);
    }

    public function mime(): ?string
    {
        return $this->headers()->get('Content-Type');
    }

    public function extension(): ?string
    {
        return (new MimeTypes)->getExtension($this->mime());
    }

    public function filename(): string
    {
        return $this->resource->getFilename();
    }

    public function size(): int
    {
        return (int) $this->headers()->get('Content-Length');
    }

    public function originalFilename(): ?string
    {
        return null;
    }

    public function stream()
    {
        return $this->resource;
    }

    public function headers(): Collection
    {
        if(!$this->headers) {
            $this->headers = collect($this->meta('wrapper_data'))
                ->map(function($item) {
                    $items = explode(': ', $item);

                    if(isset($items[1])) {
                        return [
                            'key' => $items[0],
                            'value' => $items[1]
                        ];
                    }
                })
                ->filter()
                ->keyBy('key')
                ->map(function($item) {
                    return $item['value'];
                });
        }

        return $this->headers;
    }

    public function meta($key = null, $default = null)
    {
        if(!$this->meta) {
            $this->meta = stream_get_meta_data($this->resource);
        }

        return $key ? (Arr::get($this->meta, $key) ?: $default) : $this->meta;
    }

}
