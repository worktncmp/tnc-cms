<?php

declare(strict_types=1);

use Core\Application;

$basePath = dirname(__DIR__);
$app = new Application($basePath);

return $app->boot();
