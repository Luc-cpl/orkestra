<?php

use Orkestra\Services\Hooks\Hooks;

beforeEach(function () {
    $this->hooks = new Hooks();
});

test('can register a hook', function () {
    $this->hooks->register('test', function () {
        return true;
    });

    expect($this->hooks->has('test'))->toBeTrue();
});

test('can register a hook with priority', function () {
	$this->hooks->register('test', function () {
		return true;
	}, 1);

	expect($this->hooks->has('test'))->toBeTrue();
});

test('can register a hook with a named function', function () {
	function testFunction() {
		return true;
	}
	$this->hooks->register('test', 'testFunction');

	expect($this->hooks->has('test'))->toBeTrue();
});

test('can not register a hook with a non callable', function () {
	$this->hooks->register('test', 'nonExistentFunction');
})->throws(TypeError::class);

test('can call a hook', function () {
	$test = 1;
	$this->hooks->register('test', function ($value) use (&$test) {
		expect($test)->toBe(2);
		expect($value)->toBe('hook called');
	});

	$this->hooks->register('test', function ($value) use (&$test) {
		expect($test)->toBe(1);
		expect($value)->toBe('hook called');
		$test++;
		return $value;
    }, 1);
	
	// A call must always return as void
    expect($this->hooks->call('test', 'hook called'))->toBeNull();
});

test('can query a hook', function () {
	// Bigger priority
	$this->hooks->register('test', function ($value) {
		expect($value)->toBe('Hello from hook');
		return 'Hello from last hook';
	}, 100);

	// Default priority
	$this->hooks->register('test', function ($value) {
		expect($value)->toBe('Hello World');
		return 'Hello';
	});

	// Default priority but set after the last one
    $this->hooks->register('test', function ($value) {
		expect($value)->toBe('Hello');
		return 'Hello from hook';
	});

	// Smaller priority
	$this->hooks->register('test', function ($value) {
		return $value . ' World';
    }, 1);

    expect($this->hooks->query('test', 'Hello'))->toBe('Hello from last hook');
});

test('can remove all hooks', function () {
    $this->hooks->register('test', function () {
        return true;
    });

	$this->hooks->register('test', function () {
        return true;
    }, 1);

    $this->hooks->removeAll('test');

    expect($this->hooks->has('test'))->toBeFalse();
});

test('can remove all hooks with priority', function () {
	$this->hooks->register('test', function () {
		return true;
	});

	$this->hooks->register('test', function () {
		return true;
	}, 1);

	$this->hooks->removeAll('test', 1);

	expect($this->hooks->has('test'))->toBeTrue();

	$this->hooks->removeAll('test');

	expect($this->hooks->has('test'))->toBeFalse();
});

test('can remove a hook', function () {
	$hook = function () {
		return true;
	};

	$this->hooks->register('test', $hook);
	$this->hooks->remove('test', $hook);

	expect($this->hooks->has('test'))->toBeFalse();

	$this->hooks->register('test', $hook);
	$this->hooks->register('test', fn() => false);

	$this->hooks->remove('test', $hook);

	expect($this->hooks->has('test'))->toBeTrue();
});

test('can remove a hook with priority', function () {
	$hook = function () {
		return true;
	};

	$this->hooks->register('test', $hook);
	$this->hooks->register('test', $hook, 1);

	$this->hooks->remove('test', $hook, 1);

	expect($this->hooks->has('test'))->toBeTrue();

	$this->hooks->remove('test', $hook);

	expect($this->hooks->has('test'))->toBeFalse();
});

test('can check if a hook callback exists', function () {
	$hook = function () {
		return true;
	};

	$this->hooks->register('test', $hook);

	expect($this->hooks->has('test', $hook))->toBeTrue();
});

test('can check if a hook callback does not exist', function () {
	$hook = function () {
		return true;
	};

	$this->hooks->register('test', $hook);

	expect($this->hooks->has('test', fn() => false))->toBeFalse();
});

test('can check if a hook tag has been called', function () {
	$hook = function () {
		return true;
	};

	$this->hooks->register('test', $hook);

	expect($this->hooks->did('test'))->toBe(0);
	$this->hooks->call('test');
	expect($this->hooks->did('test'))->toBe(1);
	$this->hooks->query('test', true);
	expect($this->hooks->did('test'))->toBe(2);
});

test('can check if a non existent hook tag has been called', function () {
	expect($this->hooks->did('nonExistentTag'))->toBe(0);
	$this->hooks->call('nonExistentTag');
	expect($this->hooks->did('nonExistentTag'))->toBe(1);
});

test('can get the current hook tag', function () {
	$this->hooks->register('test', function () {
		expect($this->hooks->doing('test'))->toBeTrue();
		expect($this->hooks->current())->toBe('test');
	});
	$this->hooks->call('test');
});

