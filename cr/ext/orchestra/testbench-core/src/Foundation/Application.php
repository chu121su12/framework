<?php

namespace Orchestra\Testbench\Foundation;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Arr;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\Contracts\Config as ConfigContract;
use Orchestra\Testbench\Workbench\Workbench;

/**
 * @api
 *
 * @phpstan-import-type TExtraConfig from \Orchestra\Testbench\Foundation\Config
 * @phpstan-import-type TOptionalExtraConfig from \Orchestra\Testbench\Foundation\Config
 *
 * @phpstan-type TConfig array{
 *   extra?: TOptionalExtraConfig,
 *   load_environment_variables?: bool,
 *   enabled_package_discoveries?: bool
 * }
 */
class Application
{
    use CreatesApplication {
        resolveApplication as protected resolveApplicationFromTrait;
        resolveApplicationConfiguration as protected resolveApplicationConfigurationFromTrait;
    }

    /**
     * The application base path.
     *
     * @var string|null
     */
    protected $basePath;

    /**
     * List of configurations.
     *
     * @var array<string, mixed>
     *
     * @phpstan-var TExtraConfig
     */
    protected $config = [
        'env' => [],
        'providers' => [],
        'dont-discover' => [],
        'bootstrappers' => [],
    ];

    /**
     * The application resolving callback.
     *
     * @var (callable(\Illuminate\Foundation\Application):(void))|null
     */
    protected $resolvingCallback;

    /**
     * Load Environment variables.
     *
     * @var bool
     */
    protected $loadEnvironmentVariables = false;

    /**
     * Create new application resolver.
     *
     * @param  string|null  $basePath
     * @param  (callable(\Illuminate\Foundation\Application):(void))|null  $resolvingCallback
     */
    public function __construct(/*?string */$basePath = null, /*?*/callable $resolvingCallback = null)
    {
        $basePath = backport_type_check('?string', $basePath);

        $this->basePath = $basePath;
        $this->resolvingCallback = $resolvingCallback;
    }

    /**
     * Create new application resolver.
     *
     * @param  string|null  $basePath
     * @param  (callable(\Illuminate\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     * @return static
     *
     * @phpstan-param TConfig  $options
     */
    public static function make(/*?string */$basePath = null, /*?*/callable $resolvingCallback = null, array $options = [])
    {
        $basePath = backport_type_check('?string', $basePath);

        return (new static($basePath, $resolvingCallback))->configure($options);
    }

    /**
     * Create new application resolver from configuration file.
     *
     * @param  \Orchestra\Testbench\Contracts\Config  $config
     * @param  (callable(\Illuminate\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     * @return static
     *
     * @phpstan-param TConfig  $options
     */
    public static function makeFromConfig(ConfigContract $config, /*?*/callable $resolvingCallback = null, array $options = [])
    {
        $basePath = isset($config['laravel']) ? $config['laravel'] : static::applicationBasePath();

        return (new static($config['laravel'], $resolvingCallback))->configure(array_merge($options, [
            'load_environment_variables' => file_exists("{$basePath}/.env"),
            'extra' => $config->getExtraAttributes(),
        ]));
    }

    /**
     * Create symlink to vendor path via new application instance.
     *
     * @param  string|null  $basePath
     * @param  string  $workingVendorPath
     * @return \Illuminate\Foundation\Application
     *
     * @codeCoverageIgnore
     */
    public static function createVendorSymlink(/*?string */$basePath, /*string */$workingVendorPath)/*: void*/
    {
        $basePath = backport_type_check('?string', $basePath);

        $workingVendorPath = backport_type_check('string', $workingVendorPath);

        $app = static::create(
            /*basePath: */$basePath,
            /*$resolvingCallback = */null,
            /*options: */['extra' => ['dont-discover' => ['*']]]
        );

        (new Bootstrap\CreateVendorSymlink($workingVendorPath))->bootstrap($app);

        return $app;
    }

    /**
     * Create new application instance.
     *
     * @param  string|null  $basePath
     * @param  (callable(\Illuminate\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     * @return \Illuminate\Foundation\Application
     *
     * @phpstan-param TConfig  $options
     */
    public static function create(/*?string */$basePath = null, /*?*/callable $resolvingCallback = null, array $options = [])
    {
        return static::make($basePath, $resolvingCallback, $options)->createApplication();
    }

    /**
     * Create new application instance from configuration file.
     *
     * @param  \Orchestra\Testbench\Contracts\Config  $config
     * @param  (callable(\Illuminate\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     * @return \Illuminate\Foundation\Application
     *
     * @phpstan-param TConfig  $options
     */
    public static function createFromConfig(ConfigContract $config, /*?*/callable $resolvingCallback = null, array $options = [])
    {
        return static::makeFromConfig($config, $resolvingCallback, $options)->createApplication();
    }

    /**
     * Configure the application options.
     *
     * @param  array<string, mixed>  $options
     * @return $this
     *
     * @phpstan-param TConfig  $options
     */
    public function configure(array $options)
    {
        if (isset($options['load_environment_variables']) && \is_bool($options['load_environment_variables'])) {
            $this->loadEnvironmentVariables = $options['load_environment_variables'];
        }

        if (isset($options['enables_package_discoveries']) && \is_bool($options['enables_package_discoveries'])) {
            Arr::set($options, 'extra.dont-discover', []);
        }

        /** @var TExtraConfig $config */
        $config = Arr::only(isset($options['extra']) ? $options['extra'] : [], array_keys($this->config));

        $this->config = $config;

        return $this;
    }

    /**
     * Ignore package discovery from.
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return isset($this->config['dont-discover']) ? $this->config['dont-discover'] : [];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return isset($this->config['providers']) ? $this->config['providers'] : [];
    }

    /**
     * Get package bootstrapper.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageBootstrappers($app)
    {
        if (\is_null($bootstrappers = (isset($this->config['bootstrappers']) ? $this->config['bootstrappers'] : null))) {
            return [];
        }

        return Arr::wrap($bootstrappers);
    }

    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        return tap($this->resolveApplicationFromTrait(), function ($app) {
            if (\is_callable($this->resolvingCallback)) {
                \call_user_func($this->resolvingCallback, $app);
            }
        });
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return isset($this->basePath) ? $this->basePath : static::applicationBasePath();
    }

    /**
     * Resolve application core environment variables implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationEnvironmentVariables($app)
    {
        Env::disablePutenv();

        $app->terminating(static function () {
            Env::enablePutenv();
        });

        if ($this->loadEnvironmentVariables === true) {
            $app->make(LoadEnvironmentVariables::class)->bootstrap($app);
        }

        (new Bootstrap\LoadEnvironmentVariablesFromArray(isset($this->config['env']) ? $this->config['env'] : []))->bootstrap($app);
    }

    /**
     * Resolve application core configuration implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConfiguration($app)
    {
        $this->resolveApplicationConfigurationFromTrait($app);

        (new Bootstrap\EnsuresDefaultConfiguration())->bootstrap($app);
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $kernel = Workbench::applicationConsoleKernel();
        $kernel = isset($kernel) ? $kernel : 'Orchestra\Testbench\Console\Kernel';

        if (file_exists($app->basePath('app/Console/Kernel.php')) && class_exists('App\Console\Kernel')) {
            $kernel = 'App\Console\Kernel';
        }

        $app->singleton('Illuminate\Contracts\Console\Kernel', $kernel);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $kernel = Workbench::applicationHttpKernel();
        $kernel = isset($kernel) ? $kernel : 'Orchestra\Testbench\Http\Kernel';

        if (file_exists($app->basePath('app/Http/Kernel.php')) && class_exists('App\Http\Kernel')) {
            $kernel = 'App\Http\Kernel';
        }

        $app->singleton('Illuminate\Contracts\Http\Kernel', $kernel);
    }
}
