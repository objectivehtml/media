<?php

namespace Objectivehtml\MediaManager\Support;

trait Configable {

    protected $config = [];
    
    public function config($key = null, $value = null)
    {
        return $key && !$value ? array_get($this->config, $key) : (
            !$key ? $this->getConfig() : $this->mergeConfig([
                $key => $value
            ])
        );
    }

    public function getConfig() : array
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

}
