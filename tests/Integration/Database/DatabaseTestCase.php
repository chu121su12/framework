<?php

namespace Illuminate\Tests\Integration\Database;

use Orchestra\Testbench\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        if (! env('DB_CONNECTION')) {
            $app['config']->set('database.default', 'testbench');
        }

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', isset($_SERVER['CI_DB_HOST']) ? $_SERVER['CI_DB_HOST'] : '127.0.0.1'),
            'port' => isset($_SERVER['CI_DB_MYSQL_PORT']) ? $_SERVER['CI_DB_MYSQL_PORT'] : '3306',
            'username' => isset($_SERVER['CI_DB_USERNAME']) ? $_SERVER['CI_DB_USERNAME'] : 'forge',
            'password' => isset($_SERVER['CI_DB_PASSWORD']) ? $_SERVER['CI_DB_PASSWORD'] : 'forge',
            'database' => isset($_SERVER['CI_DB_DATABASE']) ? $_SERVER['CI_DB_DATABASE'] : 'forge',
            'prefix' => '',
        ]);
    }

    protected function tearDown()/*: void*/
    {
        if ($this->app['config']->get('database.default') !== 'testbench') {
            $this->artisan('db:wipe', ['--drop-views' => true]);
        }

        parent::tearDown();
    }

    protected function supportsJson()
    {
        $version = \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);

        if (strpos($version, 'MariaDB') !== false) {
            return version_compare($version, '5.5.6', '>=');
        }

        return true;
    }
}
