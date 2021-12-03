<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    use DatabaseMigrations;

    /**
     * The current database driver.
     *
     * @return string
     */
    protected $driver;

    protected function setUp(): void
    {
        $this->beforeApplicationDestroyed(function () {
            foreach (array_keys($this->app['db']->getConnections()) as $name) {
                $this->app['db']->purge($name);
            }
        });

        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $connection = $app['config']->get('database.default');

        $this->driver = $app['config']->get("database.connections.$connection.driver");

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', isset($_SERVER['CI_DB_HOST']) ? $_SERVER['CI_DB_HOST'] : '127.0.0.1'),
            'port' => isset($_SERVER['CI_DB_MYSQL_PORT']) ? $_SERVER['CI_DB_MYSQL_PORT'] : '3306',
            'username' => isset($_SERVER['CI_DB_USERNAME']) ? $_SERVER['CI_DB_USERNAME'] : 'forge',
            'password' => isset($_SERVER['CI_DB_PASSWORD']) ? $_SERVER['CI_DB_PASSWORD'] : 'forge',
            'database' => isset($_SERVER['CI_DB_DATABASE']) ? $_SERVER['CI_DB_DATABASE'] : 'forge',
            'prefix' => '',
            'options' => [
                \PDO::ATTR_TIMEOUT => isset($_SERVER['CI_DB_OPTIONS_TIMEOUT']) ? $_SERVER['CI_DB_OPTIONS_TIMEOUT'] : 60,
            ],
        ]);
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
