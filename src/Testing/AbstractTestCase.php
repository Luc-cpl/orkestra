<?php

namespace Orkestra\Testing;

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Configuration;
use Orkestra\App as BaseApp;
use Pest\Support\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class AbstractTestCase extends BaseTestCase
{
    /**
     * Get the application configuration.
     *
     * @return array<string, mixed>
     */
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

        /** @var ConfigurationInterface */
        $config = $container->get(ConfigurationInterface::class);
        $app = new App($config);

        // Return the test app in the container
        $app->bind(BaseApp::class, $app);

        // Register a new application in each test
        $container->add(App::class, $app);
        $container->add(BaseApp::class, $app);
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
}
