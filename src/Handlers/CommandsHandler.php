<?php

namespace Orkestra\Handlers;

use Orkestra\Interfaces\HandlerInterface;
use Symfony\Component\Console\Application;

class CommandsHandler implements HandlerInterface
{
    public function __construct(
        protected Application $console,
    ) {
    }

    /**
     * Handle the current request.
     * This should be called to handle the current request from the provider.
     */
    public function handle(): void
    {
        $this->console->run();
    }
}
