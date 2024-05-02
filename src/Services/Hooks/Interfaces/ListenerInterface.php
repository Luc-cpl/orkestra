<?php

namespace Orkestra\Services\Hooks\Interfaces;

interface ListenerInterface
{
    /**
     * @return string|string[]
     */
    public function hook(): string|array;
}
