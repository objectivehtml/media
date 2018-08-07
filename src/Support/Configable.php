<?php

namespace Objectivehtml\Media\Support;

trait Configable {

    protected $config = [];

    public function config($key = null, $default = null)
    {
        return !is_null($value = array_get($this->config, $key)) ? $value : $default;
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
