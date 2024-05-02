<?php

namespace Orkestra\Services\Http\Traits;

use League\Route\Strategy\JsonStrategy;

trait RouteStrategyTrait
{
    /**
     * Set the json strategy for responses.
     *
     * @return self
     */
    public function json(): self
    {
        $this->setStrategy($this->app->get(JsonStrategy::class));
        return $this;
    }
}
