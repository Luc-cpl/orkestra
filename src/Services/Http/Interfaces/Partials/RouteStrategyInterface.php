<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

interface RouteStrategyInterface
{
    /**
     * Set the json strategy for responses.
     *
     * @return $this
     */
    public function json(): self;
}
