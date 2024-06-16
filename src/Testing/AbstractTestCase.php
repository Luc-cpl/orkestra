<?php

namespace Orkestra\Testing;

use Orkestra\Testing\Traits\HasApplicationTrait;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class AbstractTestCase extends BaseTestCase
{
    use HasApplicationTrait;
}
