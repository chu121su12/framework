<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Orchestra\Testbench\after_resolving;

trait WithLaravelMigrations
{
    use InteractsWithWorkbench;

    /**
     * Bootstrap with laravel migrations.
     *
     * @return void
     */
    protected function setUpWithLaravelMigrations()/*: void*/
    {
        if (isset(static::$cachedConfigurationForWorkbench)) {
            $attributes = static::$cachedConfigurationForWorkbench->getWorkbenchAttributes();

            /** @var bool $loadLaravelMigrations */
            $loadLaravelMigrations = isset($attributes['install']) ? $attributes['install'] : false;
        }
        else {
            $loadLaravelMigrations = false;
        }

        if (! ($loadLaravelMigrations && static::usesTestingConcern(WithWorkbench::class))) {
            if (! is_dir($this->app->basePath('migrations'))) {
                return;
            }

            if (! static::usesTestingConcern(LazilyRefreshDatabase::class) && ! static::usesTestingConcern(RefreshDatabase::class)) {
                $this->loadLaravelMigrations();
            } else {
                after_resolving($this->app, 'migrator', static function ($migrator, $app) {
                    $migrator->path($app->basePath('migrations'));
                });
            }
        }
    }
}
