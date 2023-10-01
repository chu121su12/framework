<?php

namespace Carbon\Patch;

if (\version_compare(\PHP_VERSION, '7.0.0', '<')) {
    require_once __DIR__ . '/../../../bootstrapper/DateTimeTrait.php';
} else {
    require_once __DIR__ . '/../../../bootstrapper/php7/DateTimeTrait.php';
}
