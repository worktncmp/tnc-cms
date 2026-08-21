<?php

declare(strict_types=1);

return [
    'name' => env('SESSION_NAME', 'cms_session'),
    'lifetime' => (int) env('SESSION_LIFETIME', '0'),
];
