<?php

namespace Illuminate\Tests\Integration\Database;

use Orchestra\Testbench\TestCase;

abstract class DatabaseMySqlTestCase extends DatabaseTestCase/*TestCase*/
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'mysql');
    }
}
