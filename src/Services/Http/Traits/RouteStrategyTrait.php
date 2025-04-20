<?php

namespace Orkestra\Services\Http\Traits;

use League\Route\Strategy\JsonStrategy;
use Orkestra\Services\Http\Interfaces\Partials\RouteStrategyInterface;

trait RouteStrategyTrait
{
    /**
     * Set the json strategy for responses.
     *
     * @return $this
     */
    public function json(): RouteStrategyInterface
    {
        $this->setStrategy($this->app->get(JsonStrategy::class));
        return $this;
    }
}
