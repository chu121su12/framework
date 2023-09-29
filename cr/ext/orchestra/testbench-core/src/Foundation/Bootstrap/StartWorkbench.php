<?php

namespace Orchestra\Testbench\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\Contracts\Config;
use Orchestra\Workbench\WorkbenchServiceProvider;

/**
 * @internal
 */
final class StartWorkbench
{
    public /*Config */$config;
    public /*bool */$loadWorkbenchProviders;

    /**
     * Construct a new Create Vendor Symlink bootstrapper.
     *
     * @param  \Orchestra\Testbench\Contracts\Config  $config
     * @param  bool  $loadWorkbenchProviders
     */
    public function __construct(
        /*public */Config $config,
        /*public *//*bool */$loadWorkbenchProviders = true
    ) {
        $this->loadWorkbenchProviders = backport_type_check('bool', $loadWorkbenchProviders);
        $this->config = $config;

        //
    }

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)/*: void*/
    {
        $app->singleton(Config::class, function () { return $this->config; });

        if ($this->loadWorkbenchProviders === true && class_exists(WorkbenchServiceProvider::class)) {
            $app->register(WorkbenchServiceProvider::class);
        }
    }
}
