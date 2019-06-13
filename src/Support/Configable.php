<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Arr;

trait Configable {

    protected $config = [];

    public function config($key = null, $default = null)
    {
        return !is_null($value = Arr::get($this->config, $key)) ? $value : $default;
    }

    public function getConfig() : array
    {
        return $this->config;
    }

    public function setConfig(array $key)
    {
        $this->config = $key;
    }

    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

}
