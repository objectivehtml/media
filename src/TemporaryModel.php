<?php

namespace Objectivehtml\Media;

class TemporaryModel extends Model
{
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->disk = app(MediaService::class)->config('temp.disk');
        $this->context = app(MediaService::class)->config('temp.context');
    }

}
