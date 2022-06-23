<?php

namespace Carbon\Patch;

if (\version_compare(\PHP_VERSION, '7.1.0', '<')) {
    require_once __DIR__ . '/../../../bootstrapper/BackportMicrosecond.php';
} else {
    require_once __DIR__ . '/../../../bootstrapper/php71/BackportMicrosecond.php';
}
