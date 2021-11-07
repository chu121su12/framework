<?php

namespace Illuminate\Tests\Integration\Auth;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as FoundationUser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

/**
 * @requires extension pdo_mysql
 */
class ApiAuthenticationWithEloquentTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');

        // Auth configuration
        $app['config']->set('auth.defaults.guard', 'api');
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('auth.guards.api', [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ]);

        // Database configuration
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => isset($_SERVER['CI_DB_AUTH_DRIVER']) ? $_SERVER['CI_DB_AUTH_DRIVER'] : 'mysql',
            'host' => env('DB_HOST', isset($_SERVER['CI_DB_HOST']) ? $_SERVER['CI_DB_HOST'] : '127.0.0.1'),
            'port' => isset($_SERVER['CI_DB_MYSQL_PORT']) ? $_SERVER['CI_DB_MYSQL_PORT'] : '3306',
            'username' => 'root',
            'password' => 'invalid-credentials',
            'database' => isset($_SERVER['CI_DB_DATABASE']) ? $_SERVER['CI_DB_DATABASE'] : 'forge',
            'prefix' => '',
        ]);
    }

    public function testAuthenticationViaApiWithEloquentUsingWrongDatabaseCredentialsShouldNotCauseInfiniteLoop()
    {
        Route::get('/auth', function () {
            return 'success';
        })->middleware('auth:api');

        $this->expectException(QueryException::class);

        $this->expectExceptionMessage("Access denied for user 'root'@");

        try {
            $this->withoutExceptionHandling()->get('/auth', ['Authorization' => 'Bearer whatever']);
        } catch (QueryException $e) {
            if (Str::startsWith($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                $this->markTestSkipped('MySQL instance required.');
            }

            throw $e;
        }
    }
}

class User extends FoundationUser
{
    //
}
