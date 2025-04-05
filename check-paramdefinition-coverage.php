<?php

// Load Composer autoloader
require __DIR__ . '/vendor/autoload.php';

$fileName = __DIR__ . '/src/Services/Http/Entities/ParamDefinition.php';

// Print the targeted lines for inspection
echo "Checking lines in ParamDefinition.php:\n";
$lines = file($fileName);

// Line 94
echo "\nLine 94:\n";
echo "Line 94: " . trim($lines[93]) . "\n";

// Print the context around this line
echo "\nContext (lines 90-100):\n";
for ($i = 89; $i <= 99; $i++) {
    echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
}
