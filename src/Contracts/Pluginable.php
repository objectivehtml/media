<?php

namespace Objectivehtml\Media\Contracts;

interface Pluginable {

    public function plugins(): array;

    public function plugin(Plugin $plugin): self;

}
