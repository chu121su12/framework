<?php

namespace Illuminate\Tests\Integration\Generators;

class ProviderMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Providers/FooServiceProvider.php',
    ];

    public function testItCanGenerateServiceProviderFile()
    {
        $this->artisan('make:provider', ['name' => 'FooServiceProvider'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Providers;',
            'use Illuminate\Support\ServiceProvider;',
            'class FooServiceProvider extends ServiceProvider',
            'public function register()',
            'public function boot()',
        ], 'app/Providers/FooServiceProvider.php');

        if (file_exists($this->app->getBootstrapProvidersPath()) && is_array(require $this->app->getBootstrapProvidersPath())) {
            $this->assertEquals(require $this->app->getBootstrapProvidersPath(), [
                'App\Providers\FooServiceProvider',
            ]);
        }
    }
}
