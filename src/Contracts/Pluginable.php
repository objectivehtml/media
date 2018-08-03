<?php

namespace Objectivehtml\MediaManager\Contracts;

interface Pluginable {

    public function plugins(): array;

    public function plugin(Plugin $plugin): self;

}
