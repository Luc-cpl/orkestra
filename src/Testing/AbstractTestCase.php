<?php

namespace Orkestra\Testing;

use Orkestra\App;
use Orkestra\Configuration;
use Orkestra\Entities\EntityFactory;
use Orkestra\Interfaces\ConfigurationInterface;
use Pest\Support\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class AbstractTestCase extends BaseTestCase
{
    protected function getApplicationConfig(): array
    {
        return [
            'env'  => 'testing',
            'root' => getcwd(),
        ];
    }

    /**
     * Creates the application.
     */
    protected function createApplication(): void
    {
        $container = Container::getInstance();
        $container->add(ConfigurationInterface::class, new Configuration($this->getApplicationConfig()));

        $app = new App($container->get(ConfigurationInterface::class));

        // Register a new application in each test
        $container->add(App::class, $app);
        $container->add(EntityFactory::class, $app->get(EntityFactory::class, ['useFaker' => true]));
    }

    /**
     * Setup the test environment.
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->createApplication();

        require_once __DIR__ . '/_functions.php';

        parent::setUp();
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between setUp() and test.
     */
    protected function assertPreConditions(): void
    {
        $container = Container::getInstance();
        $app = $container->get(App::class);
        $app->boot();

        parent::assertPreConditions();
    }
}