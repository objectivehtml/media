<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Collection;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Contracts\Plugin;
use Objectivehtml\Media\Contracts\Pluginable as PluginableInterface;

trait Pluginable {

    protected $plugins;

    public function plugins(): Collection
    {
        $this->instantiatePlugins();

        return $this->plugins->filter(function($plugin) {
            return $plugin->doesMeetRequirements();
        });
    }

    public function plugin(Plugin $plugin): PluginableInterface
    {
        $this->instantiatePlugins();
        $this->plugins->push($plugin);

        return $this;
    }

    public function jobs(Model $model)
    {
        return $this->plugins()->filter(function($plugin) use ($model) {
            return $plugin->doesApply($model->mime, $model->extension);
        })->map(function($plugin) use ($model) {
            return $plugin->jobs($model);
        })
        ->flatten(1);
    }

    public function conversions(Model $model)
    {
        return $this->plugins()->filter(function($plugin) use ($model) {
            return $plugin->doesApply($model->mime, $model->extension);
        })->map(function($plugin) use ($model) {
            return $plugin->conversions($model);
        })
        ->flatten(1)
        ->concat($model->conversions)
        ->map(function($conversion) {
            if(is_array($conversion)) {
                return $conversion[0]::make(...isset($conversion[1]) ? $conversion[1] : []);
            }

            return $conversion;
        });
    }

    public function generators(Model $model)
    {
        return $this->plugins()->filter(function($plugin) use ($model) {
            return $plugin->doesApply($model->mime, $model->extension);
        })->map(function($plugin) use ($model) {
            return $plugin->generators($model);
        })->flatten(1);
    }

    protected function instantiatePlugins()
    {
        if(!$this->plugins) {
            $this->plugins = collect(app(MediaService::class)->config('plugins'))->map(function($class) {
                return new $class();
            });
        }
    }

}
