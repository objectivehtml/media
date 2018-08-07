<?php

namespace Objectivehtml\Media\Contracts;

interface Configable {

    public function config($key = null, $default = null);

    public function getConfig(): array;

    public function setConfig(array $config);

    public function mergeConfig(array $config);

}
