<?php

namespace Doctrine\Patch;

if (\version_compare(\PHP_VERSION, '7.0.0', '<')) {
    require_once __DIR__ . '/../../bootstrapper/PDOStatementTrait.php';
} else {
    require_once __DIR__ . '/../../bootstrapper/php8/PDOStatementTrait.php';
}
