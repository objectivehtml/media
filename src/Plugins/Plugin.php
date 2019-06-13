<?php

namespace Objectivehtml\Media\Plugins;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\PluginObserver;
use Objectivehtml\Media\Contracts\Plugin as PluginInterface;

abstract class Plugin implements PluginInterface {

    use Applyable;

    /**
     * Return the jobs that should be triggered for each new media model.
     *
     * @return array
     */
    public function jobs(Model $model): array
    {
        return [];
    }

    /**
     * Return the filters that should be triggered for each new media model.
     *
     * @return array
     */
    public function filters(Model $model): array
    {
        return [];
    }

    /**
     * Return the conversions that should be triggered for each new media model.
     *
     * @return array
     */
    public function conversions(Model $model): array
    {
        return [];
    }

    /**
     * Determine if the plugin's required PHP requirements are met.
     *
     * @return bool
     */
    public function doesMeetRequirements(): bool
    {
        return true;
    }


    /**
     * Get the plugin observer instance.
     *
     * @return bool
     */
    public function observe(string $className)
    {
        return new PluginObserver($this, $className);
    }
}
