<?php

namespace Doctrine\Patch;

if (\version_compare(\PHP_VERSION, '8.0.0', '<')) {
    require_once __DIR__ . '/../../bootstrapper/PDOConnectionTrait.php';
} else {
    require_once __DIR__ . '/../../bootstrapper/php8/PDOConnectionTrait.php';
}
