<?php

namespace Objectivehtml\MediaManager\Contracts;

interface Configable {

    public function config($key = null, $value = null);

    public function getConfig(): array;

    public function setConfig(array $config);

    public function mergeConfig(array $config);

}
