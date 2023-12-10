<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Arr;
use Orchestra\Testbench\Workbench\Workbench;

trait InteractsWithWorkbenchAndTestCase
{
    use InteractsWithPHPUnitMethodOnly;

    /**
     * Get Application's base path.
     *
     * @return string|null
     */
    public static function applicationBasePathUsingWorkbench()
    {
        if (! static::usesTestingConcern()) {
            return isset($_ENV['APP_BASE_PATH']) ? $_ENV['APP_BASE_PATH'] : null;
        }

        return isset($_ENV['APP_BASE_PATH']) ? $_ENV['APP_BASE_PATH'] : (isset($_ENV['TESTBENCH_APP_BASE_PATH']) ? $_ENV['TESTBENCH_APP_BASE_PATH'] : null);
    }

    /**
     * Ignore package discovery from.
     *
     * @return array<int, string>|null
     */
    public function ignorePackageDiscoveriesFromUsingWorkbench()
    {
        if (property_exists($this, 'enablesPackageDiscoveries') && \is_bool($this->enablesPackageDiscoveries)) {
            return $this->enablesPackageDiscoveries === false ? ['*'] : [];
        }

        if (!static::usesTestingConcern(WithWorkbench::class)) {
            return null;
        }

        $cachedConfigurationForWorkbench = static::cachedConfigurationForWorkbench();

        if (isset($cachedConfigurationForWorkbench)) {
            $extraAttributes = $cachedConfigurationForWorkbench->getExtraAttributes();
            if (isset($extraAttributes) && isset($extraAttributes['dont-discover'])) {
                return $extraAttributes['dont-discover'];
            }
        }

        return [];
    }

    /**
     * Get package bootstrapper.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>|null
     */
    protected function getPackageBootstrappersUsingWorkbench($app)
    {
        $bootstrappers = null;
        $cachedConfigurationForWorkbench = static::cachedConfigurationForWorkbench();

        if (isset($cachedConfigurationForWorkbench)) {
            $extraAttributes = $cachedConfigurationForWorkbench->getExtraAttributes();
            if (empty(isset($extraAttributes) && ($bootstrappers = isset($extraAttributes['bootstrappers']) ? $extraAttributes['bootstrappers'] : null))) {
                return null;
            }
        }

        return static::usesTestingConcern(WithWorkbench::class)
            ? Arr::wrap($bootstrappers)
            : [];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>|null
     */
    protected function getPackageProvidersUsingWorkbench($app)
    {
        $cachedConfigurationForWorkbench = static::cachedConfigurationForWorkbench();

        if (isset($cachedConfigurationForWorkbench)) {
            $extraAttributes = $cachedConfigurationForWorkbench->getExtraAttributes();
            if (empty(isset($extraAttributes) && isset($extraAttributes['providers']) ? $extraAttributes['providers'] : null)) {
                return null;
            }
        }

        return static::usesTestingConcern(WithWorkbench::class)
            ? Arr::wrap($providers)
            : [];
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string
     */
    protected function applicationConsoleKernelUsingWorkbench($app)/*: string*/
    {
        if (static::usesTestingConcern(WithWorkbench::class)) {
            $kernel = Workbench::applicationConsoleKernel();
            return isset($kernel) ? $kernel : 'Orchestra\Testbench\Console\Kernel';
        }

        return 'Orchestra\Testbench\Console\Kernel';
    }

    /**
     * Get application HTTP Kernel implementation using Workbench.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string
     */
    protected function applicationHttpKernelUsingWorkbench($app)/*: string*/
    {
        if (static::usesTestingConcern(WithWorkbench::class)) {
            $kernel = Workbench::applicationHttpKernel();
            return isset($kernel) ? $kernel : 'Orchestra\Testbench\Http\Kernel';
        }

        return 'Orchestra\Testbench\Http\Kernel';
    }

    /**
     * Get application HTTP exception handler using Workbench.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string
     */
    protected function applicationExceptionHandlerUsingWorkbench($app)/*: string*/
    {
        if (static::usesTestingConcern(WithWorkbench::class)) {
            $kernel = Workbench::applicationExceptionHandler();
            return isset($kernel) ? $kernel : 'Orchestra\Testbench\Exceptions\Handler';
        }

        return 'Orchestra\Testbench\Exceptions\Handler';
    }

    /**
     * Define or get the cached uses for test case.
     *
     * @return \Orchestra\Testbench\Contracts\Config|null
     */
    public static function cachedConfigurationForWorkbench()
    {
        return Workbench::configuration();
    }

    /**
     * Prepare the testing environment before the running the test case.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function setupBeforeClassUsingWorkbench()/*: void*/
    {
        /** @var array{laravel: string|null} $config */
        $config = static::cachedConfigurationForWorkbench();

        if (
            ! \is_null($config['laravel'])
            && isset(static::$cachedTestCaseUses[WithWorkbench::class])
        ) {
            $_ENV['TESTBENCH_APP_BASE_PATH'] = $config['laravel'];
        }
    }

    /**
     * Clean up the testing environment before the next test case.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function teardownAfterClassUsingWorkbench()/*: void*/
    {
        unset($_ENV['TESTBENCH_APP_BASE_PATH']);
    }
}
