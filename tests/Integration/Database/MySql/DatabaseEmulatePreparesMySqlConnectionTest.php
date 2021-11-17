<?php

namespace Illuminate\Tests\Integration\Database\MySql;

use PDO;

/**
 * @requires extension pdo_mysql
 * @requires OS Linux|Darwin
 */
class DatabaseEmulatePreparesMySqlConnectionTest extends DatabaseMySqlConnectionTest
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.mysql.options', [
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_TIMEOUT => isset($_SERVER['CI_DB_OPTIONS_TIMEOUT']) ? $_SERVER['CI_DB_OPTIONS_TIMEOUT'] : 60,
        ]);
    }
}
