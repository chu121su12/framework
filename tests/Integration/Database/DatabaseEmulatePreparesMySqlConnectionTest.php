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
            'driver' => isset($_SERVER['CI_DB_DRIVER']) ? $_SERVER['CI_DB_DRIVER'] : 'mysql',
            'host' => env('DB_HOST', isset($_SERVER['CI_DB_HOST']) ? $_SERVER['CI_DB_HOST'] : '127.0.0.1'),
            'port' => isset($_SERVER['CI_DB_PORT']) ? $_SERVER['CI_DB_PORT'] : '3306',
            'username' => isset($_SERVER['CI_DB_USERNAME']) ? $_SERVER['CI_DB_USERNAME'] : 'forge',
            'password' => isset($_SERVER['CI_DB_PASSWORD']) ? $_SERVER['CI_DB_PASSWORD'] : 'forge',
            'database' => isset($_SERVER['CI_DB_DATABASE']) ? $_SERVER['CI_DB_DATABASE'] : 'forge',
            'prefix' => '',
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true,
            ]
        ]);
    }
}
