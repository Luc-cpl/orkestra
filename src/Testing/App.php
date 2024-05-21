<?php

namespace Orkestra\Testing;

use Orkestra\App as BaseApp;

/**
 * This class is useful for testing purposes.
 * It extends the default App class and boot the application
 * instead of throwing an `BadMethodCallException`.
 */
class App extends BaseApp
{
    protected function bootGate(bool $booted = false): void
    {
        try {
            parent::bootGate($booted);
            return;
        } catch (\BadMethodCallException $e) {
            // throw in case of app has already been booted
            if (!$booted) {
                throw $e;
            }
        }
        // boot the application
        $this->boot();
    }
}
