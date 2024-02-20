<?php

namespace Illuminate\Tests\Integration\Database\MariaDb;

use PDO;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @requires extension pdo_mysql
 * @requires OS Linux|Darwin
 */
#[RequiresOperatingSystem('Linux|Darwin')]
#[RequiresPhpExtension('pdo_mysql')]
class DatabaseEmulatePreparesMariaDbConnectionTest extends DatabaseMariaDbConnectionTest
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.mariadb.options', [
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);
    }
}
