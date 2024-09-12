<?php

namespace Illuminate\Tests\Integration\Console;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @requires OS Linux|Darwin
 */
#[RequiresOperatingSystem('Linux|DAR')]
class ConcurrencyTest extends TestCase
{
    protected function setUp()/*: void*/
    {
        $route = <<<PHP
<?php
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Route;

Route::any('/concurrency', function () {
    return Concurrency::run([
        function () { return 1 + 1; },
        function () { return 2 + 2; },
    ]);
});
PHP;

        $this->defineCacheRoutes($route);

        parent::setUp();
    }

    public function testWorkCanBeDistributed()
    {
        $response = $this->get('concurrency')
            ->assertOk();

        list($first, $second) = $response->original;

        $this->assertEquals(2, $first);
        $this->assertEquals(4, $second);
    }
}
