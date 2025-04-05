<?php

// Load Composer autoloader
require __DIR__ . '/vendor/autoload.php';

$fileName = __DIR__ . '/src/Services/Http/Middleware/ValidationMiddleware.php';

// Print the targeted lines for inspection
echo "Checking lines in ValidationMiddleware.php:\n";
$lines = file($fileName);

// Lines 41-43
echo "\nLines 41-43:\n";
for ($i = 40; $i <= 42; $i++) {
    echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
}

// Line 73
echo "\nLine 73:\n";
echo "Line 73: " . trim($lines[72]) . "\n";

// Lines 96-98
echo "\nLines 96-98:\n";
for ($i = 95; $i <= 97; $i++) {
    echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
}

echo "\nRunning tests for ValidationMiddlewareAdditionalTest.php\n";
passthru('XDEBUG_MODE=coverage ' . __DIR__ . '/vendor/bin/pest tests/Unit/Services/Http/Middleware/ValidationMiddlewareAdditionalTest.php');
