<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\Contracts\Plugin;

class PluginObserver {

    protected $plugin;

    protected $className;
    
    public function __construct(Plugin $plugin, string $className)
    {
        // Set the plugin attribute
        $this->plugin = $plugin;

        // Sett the className attribute
        $this->className = $className;

        // When registering a model observer, we will spin through the possible events
        // and determine if this observer has that method. If it does, we will hook
        // it into the model's event system, making it convenient to watch these.
        foreach((new $className)->getObservableEvents() as $event) {
            if(method_exists($this->plugin, $event)) {
                $className::registerModelEvent($event, function(Model $model) use ($event) {
                    if($this->plugin->doesApplyToModel($model)) {
                        $this->plugin->$event($model);
                    }
                });
            }
        }
    }

}
