<?php

namespace Objectivehtml\Media\Services;

use Objectivehtml\Media\Support\Configable;
use Illuminate\Contracts\Filesystem\Factory;
use Objectivehtml\Media\Contracts\Configable as ConfigableInterface;

abstract class Service implements ConfigableInterface {

    use Configable;

    protected $filesystem;

    public function __construct(Factory $filesystem, array $config = [])
    {
        $this->filesystem = $filesystem;
        $this->mergeConfig($config);
    }

}
