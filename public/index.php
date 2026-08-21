<?php

declare(strict_types=1);

use Core\Autoloader;

$basePath = dirname(__DIR__);

require $basePath . '/core/Autoloader.php';
Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

$app = require $basePath . '/bootstrap/app.php';
$app->run();
