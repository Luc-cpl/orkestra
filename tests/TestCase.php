<?php

namespace Tests;

use Orkestra\Testing\AbstractTestCase;

abstract class TestCase extends AbstractTestCase
{
    protected function getApplicationConfig(): array
    {
        $parent = parent::getApplicationConfig();
        return array_merge($parent, [
            'root' => getcwd() . '/tests/app-test',
        ]);
    }
}
