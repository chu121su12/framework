<?php

namespace Illuminate\Tests\Integration\Foundation\Support\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class RouteServiceProviderHealthTest extends TestCase
{
    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withRouting(
                /*?Closure $using = */null,
                /*web: */__DIR__.'/fixtures/web.php',
                /*?string $api = */null,
                /*?string $commands = */null,
                /*?string $channels = */null,
                /*?string $pages = */null,
                /*health: */'/up'
            )->create();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', Str::random(32));
    }

    public function test_it_can_load_health_page()
    {
        $this->get('/up')->assertOk();
    }
}
