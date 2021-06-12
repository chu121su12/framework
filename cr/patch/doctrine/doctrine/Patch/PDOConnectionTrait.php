<?php

namespace Doctrine\Patch;

use Doctrine\DBAL\Driver\PDOException;
use PDOException as PhpPDOException;

if (\version_compare(\PHP_VERSION, '8.0.0', '<')) {
    require_once __DIR__ . '/../../bootstrapper/PDOConnectionComplianceTrait.php';
} else {
    require_once __DIR__ . '/../../bootstrapper/php8/PDOConnectionComplianceTrait.php';
}

trait PDOConnectionTrait
{
    use PDOConnectionComplianceTrait;

    protected function query_()
    {
        $args = func_get_args();

        try {
            switch (count($args)) {
                case 4: return parent::query($args[0], $args[1], $args[2], $args[3]);
                case 3: return parent::query($args[0], $args[1], $args[2]);
                case 2: return parent::query($args[0], $args[1]);
                default: return parent::query($args[0]);
            }
        } catch (PhpPDOException $exception) {
            throw new PDOException($exception);
        }
    }
}
