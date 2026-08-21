<?php

declare(strict_types=1);

use Core\Application;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';
$map = $app->pages()->rebuildCache();

echo 'Cached ' . count($map) . " page routes.\n";
foreach ($map as $url => $file) {
    echo '  ' . $url . ' -> ' . $file . "\n";
}
