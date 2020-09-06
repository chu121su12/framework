<?php

namespace Illuminate\Tests\Integration\Database;

use Orchestra\Testbench\TestCase;

abstract class DatabaseMySqlTestCase extends TestCase
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
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        if (! isset($_SERVER['CI']) || windows_os()) {
            $this->markTestSkipped('This test is only executed on CI.');
        }
    }
}
