<?php

namespace Illuminate\Tests\Integration\Database;

use PDO;

/**
 * @requires extension pdo_mysql
 */
class DatabaseEmulatePreparesMySqlConnectionTest extends DatabaseMySqlConnectionTest
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'username' => 'forge',
            'password' => 'forge',
            'database' => 'forge',
            'prefix' => '',
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true,
            ]
        ]);
    }
}
