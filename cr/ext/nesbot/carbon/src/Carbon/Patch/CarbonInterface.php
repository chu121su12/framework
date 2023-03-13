<?php

namespace Carbon\Patch;

if (\version_compare(\PHP_VERSION, '8.0.0', '<')) {
    require_once __DIR__ . '/../../../bootstrapper/CarbonInterface.php';
} else {
    require_once __DIR__ . '/../../../bootstrapper/php8/CarbonInterface.php';
}
