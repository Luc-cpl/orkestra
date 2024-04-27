<?php

use Orkestra\Providers\ViewProvider;

beforeEach(function () {
	app()->provider(ViewProvider::class);
});

