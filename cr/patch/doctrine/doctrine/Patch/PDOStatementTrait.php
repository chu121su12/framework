<?php

namespace Doctrine\Patch;

use Doctrine\DBAL\Driver\PDOException;
use PDOException as PhpPDOException;

if (\version_compare(\PHP_VERSION, '8.0.0', '<')) {
    require_once __DIR__ . '/../../bootstrapper/PDOStatementComplianceTrait.php';
} else {
    require_once __DIR__ . '/../../bootstrapper/php8/PDOStatementComplianceTrait.php';
}

trait PDOStatementTrait
{
    use PDOStatementComplianceTrait;

    protected function setFetchMode_($fetchMode, $arg2 = null, $arg3 = null)
    {
        // This thin wrapper is necessary to shield against the weird signature
        // of PDOStatement::setFetchMode(): even if the second and third
        // parameters are optional, PHP will not let us remove it from this
        // declaration.
        try {
            if ($arg2 === null && $arg3 === null) {
                return parent::setFetchMode($fetchMode);
            }

            if ($arg3 === null) {
                return parent::setFetchMode($fetchMode, $arg2);
            }

            return parent::setFetchMode($fetchMode, $arg2, $arg3);
        } catch (PhpPDOException $exception) {
            throw new PDOException($exception);
        }
    }

    protected function fetchAll_($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        try {
            if ($fetchMode === null && $fetchArgument === null && $ctorArgs === null) {
                return parent::fetchAll();
            }

            if ($fetchArgument === null && $ctorArgs === null) {
                return parent::fetchAll($fetchMode);
            }

            if ($ctorArgs === null) {
                return parent::fetchAll($fetchMode, $fetchArgument);
            }

            return parent::fetchAll($fetchMode, $fetchArgument, $ctorArgs);
        } catch (PhpPDOException $exception) {
            throw new PDOException($exception);
        }
    }
}
