<?php

namespace Orchestra\Testbench\Console;

use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Env;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Commander
{
    use CreatesApplication {
        resolveApplication as protected resolveApplicationFromTrait;
        getBasePath as protected getBasePathFromTrait;
    }

    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * List of configurations.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Working path.
     *
     * @var string
     */
    protected $workingPath;

    /**
     * Construct a new Commander.
     *
     * @param  array  $config
     * @param  string  $workingPath
     */
    public function __construct(array $config, $workingPath)
    {
        $workingPath = cast_to_string($workingPath);

        $this->config = $config;
        $this->workingPath = $workingPath;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $laravel = $this->laravel();
        $kernel = $laravel->make(ConsoleKernel::class);

        $status = $kernel->handle(
            $input = new ArgvInput(), new ConsoleOutput()
        );

        $kernel->terminate($input, $status);

        exit($status);
    }

    /**
     * Create Laravel application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function laravel()
    {
        if (! $this->app) {
            $this->app = $this->createApplication();
        }

        return $this->app;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return isset($this->config['providers']) ? $this->config['providers'] : [];
    }

    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        return \tap($this->resolveApplicationFromTrait(), function () {
            $this->createDotenv()->load();
        });
    }

    /**
     * Create a Dotenv instance.
     */
    protected function createDotenv()
    {
        $laravelBasePath = $this->getBasePath();

        if (\file_exists($laravelBasePath.'/.env')) {
            return Dotenv::create(
                Env::getRepository(), $laravelBasePath.'/', '.env'
            );
        }

        $path = \tempnam(\sys_get_temp_dir(), 'testbench');

        \file_put_contents(
            $path,
            \implode("\n", isset($this->config['env']) ? $this->config['env'] : [])
        );

        \register_shutdown_function(function () use ($path) {
            if (\file_exists($path)) {
                \unlink($path);
            }
        });

        return Dotenv::create(
            Env::getRepository(), \dirname($path) . '/', \basename($path)
        );
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        $laravelBasePath = isset($this->config['laravel']) ? $this->config['laravel'] : null;

        if (! \is_null($laravelBasePath)) {
            return \str_replace('./', $this->workingPath.'/', $laravelBasePath);
        }

        return $this->getBasePathFromTrait();
    }
}
