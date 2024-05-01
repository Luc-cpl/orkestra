<?php

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Providers\CommandsProvider;
use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Commands\MiddlewareListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class TestProvider1 implements ProviderInterface
{
    public array $middleware = [
        'middleware1' => 'Middleware1',
        'middleware3' => 'Middleware3',
        'middleware4' => 'Middleware4',
    ];
    public function register(App $app): void
    {
    }
    public function boot(App $app): void
    {
    }
}

class TestProvider2 implements ProviderInterface
{
    public array $middleware = [
        'middleware4' => 'Middleware4',
        'middleware5' => 'Middleware5',
    ];
    public function register(App $app): void
    {
    }
    public function boot(App $app): void
    {
    }
}

beforeEach(function () {
    app()->provider(TestProvider1::class);
    app()->provider(TestProvider2::class);
    app()->provider(CommandsProvider::class);
    app()->provider(HttpProvider::class);
    app()->config()->set('middleware', [
        'middleware1' => 'Middleware1',
        'middleware2' => 'Middleware2',
    ]);
});

test('can list the available middleware', function () {
    $tester = new CommandTester(app()->get(MiddlewareListCommand::class));
    $tester->execute([], ['interactive' => false]);

    $outputLines = explode(PHP_EOL, $tester->getDisplay());

    // Check the number of lines in the output
    expect(count($outputLines))->toBe(12);

    // Check the content of each line
    expect($outputLines[0]) ->toBe('Available middleware options:');
    expect($outputLines[1]) ->toBe('');
    expect($outputLines[2]) ->toBe('+-------------+-------------+---------------+');
    expect($outputLines[3]) ->toBe('| Alias       | Middleware  | Placed By     |');
    expect($outputLines[4]) ->toBe('+-------------+-------------+---------------+');
    expect($outputLines[5]) ->toBe('| middleware1 | Middleware1 | configuration |');
    expect($outputLines[6]) ->toBe('| middleware2 | Middleware2 | configuration |');
    expect($outputLines[7]) ->toBe('| middleware3 | Middleware3 | TestProvider1 |');
    expect($outputLines[8]) ->toBe('| middleware4 | Middleware4 | TestProvider1 |');
    expect($outputLines[9]) ->toBe('| middleware5 | Middleware5 | TestProvider2 |');
    expect($outputLines[10])->toBe('+-------------+-------------+---------------+');
    expect($outputLines[11])->toBe('');
});
