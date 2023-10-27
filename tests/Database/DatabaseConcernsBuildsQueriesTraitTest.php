<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Concerns\BuildsQueries;
use PHPUnit\Framework\TestCase;

class DatabaseConcernsBuildsQueriesTraitTest_testTapCallbackInstance_class
        {
            use BuildsQueries;
        }

class DatabaseConcernsBuildsQueriesTraitTest extends TestCase
{
    public function testTapCallbackInstance()
    {
        $mock = new DatabaseConcernsBuildsQueriesTraitTest_testTapCallbackInstance_class;

        $mock->tap(function ($builder) use ($mock) {
            $this->assertEquals($mock, $builder);
        });
    }
}
