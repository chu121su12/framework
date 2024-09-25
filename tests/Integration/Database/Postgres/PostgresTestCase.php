<?php

namespace Illuminate\Tests\Integration\Database\Postgres;

use Illuminate\Tests\Integration\Database\DatabaseTestCase;
use Orchestra\Testbench\Attributes\RequiresDatabase;

#[RequiresDatabase('pgsql')]
abstract class PostgresTestCase extends DatabaseTestCase
{
    protected function attributeBp()
    {
        return [
            'database' => ['pgsql'],
        ];
    }

    //
}
