<?php

namespace Objectivehtml\MediaManager\Plugins;

use Illuminate\Database\Eloquent\Model;
use Objectivehtml\MediaManager\Support\Applyable;
use Objectivehtml\MediaManager\Contracts\Plugin as PluginInterface;

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
     * Return the generators that should be triggered for each new media model.
     *
     * @return array
     */
    public function generators(Model $model): array
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

}
