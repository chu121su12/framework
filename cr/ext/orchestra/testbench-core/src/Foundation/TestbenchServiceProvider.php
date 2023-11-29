<?php

namespace Orchestra\Testbench\Foundation;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as CollisionTestCommand;

class TestbenchServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()/*: void*/
    {
        $workingPath = \defined('TESTBENCH_WORKING_PATH') ? TESTBENCH_WORKING_PATH : null;

        AboutCommand::add('Testbench', function () use ($workingPath) { return array_filter([
            'Core Version' => $this->getOwnVersion(),
            'Dusk Version' => InstalledVersions::isInstalled('orchestra/testbench-dusk') ? InstalledVersions::getPrettyVersion('orchestra/testbench-dusk') : null,
            'Skeleton Path' => str_replace($workingPath, '', $this->app->basePath()),
            'Version' => InstalledVersions::isInstalled('orchestra/testbench') ? InstalledVersions::getPrettyVersion('orchestra/testbench') : null,
        ]); });
    }

    protected function getOwnVersion()
    {
        try {
            return InstalledVersions::getPrettyVersion('orchestra/testbench-core');
        }
        catch (\OutOfBoundsException $_e) {
            return \file_get_contents(__DIR__.'/../../version.text');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()/*: void*/
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                $this->isCollisionDependenciesInstalled()
                    ? Console\TestCommand::class
                    : Console\TestFallbackCommand::class,
                Console\CreateSqliteDbCommand::class,
                Console\DevToolCommand::class,
                Console\DropSqliteDbCommand::class,
                Console\PurgeSkeletonCommand::class,
                Console\ServeCommand::class,
            ]);
        }
    }

    /**
     * Check if the parallel dependencies are installed.
     *
     * @return bool
     */
    protected function isCollisionDependenciesInstalled()/*: bool*/
    {
        return class_exists(CollisionTestCommand::class);
    }
}
