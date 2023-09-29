<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Arr;
use Orchestra\Testbench\Foundation\Config;
use Orchestra\Testbench\Foundation\Env;

trait InteractsWithWorkbench
{
    use InteractsWithPHPUnit;

    /**
     * The cached test case configuration.
     *
     * @var \Orchestra\Testbench\Contracts\Config|null
     */
    protected static $cachedConfigurationForWorkbench;

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

        if (static::usesTestingConcern(WithWorkbench::class)) {
            if (isset(static::$cachedConfigurationForWorkbench)) {
                $attributes = static::$cachedConfigurationForWorkbench->getExtraAttributes();

                if (isset($attributes['dont-discover'])) {
                    return $attributes['dont-discover'];
                }
            }

            return [];
        }

        return null;
    }

    /**
     * Get package bootstrapper.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>|null
     */
    protected function getPackageBootstrappersUsingWorkbench($app)
    {
        $attributes = isset(static::$cachedConfigurationForWorkbench)
            ? static::$cachedConfigurationForWorkbench->getExtraAttributes()
            : [];

        if (empty($bootstrappers = (isset($attributes['bootstrappers']) ? $attributes['bootstrappers'] : null))) {
            return null;
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
        $attributes = isset(static::$cachedConfigurationForWorkbench)
            ? static::$cachedConfigurationForWorkbench->getExtraAttributes()
            : [];

        if (empty($providers = (isset($attributes['providers']) ? $attributes['providers'] : null))) {
            return null;
        }

        return static::usesTestingConcern(WithWorkbench::class)
            ? Arr::wrap($providers)
            : [];
    }

    /**
     * Define or get the cached uses for test case.
     *
     * @return \Orchestra\Testbench\Contracts\Config|null
     */
    public static function cachedConfigurationForWorkbench()
    {
        if (! isset(static::$cachedConfigurationForWorkbench)) {
            switch (true) {
                case \defined('TESTBENCH_WORKING_PATH'):
                    $cachedConfigurationForWorkbench = TESTBENCH_WORKING_PATH;
                    break;

                case ! \is_null(Env::get('TESTBENCH_WORKING_PATH')):
                    $cachedConfigurationForWorkbench = Env::get('TESTBENCH_WORKING_PATH');
                    break;

                default:
                    $cachedConfigurationForWorkbench = getcwd();
                    break;
            }

            static::$cachedConfigurationForWorkbench = Config::cacheFromYaml($cachedConfigurationForWorkbench);
        }

        return static::$cachedConfigurationForWorkbench;
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
        static::$cachedConfigurationForWorkbench = null;

        unset($_ENV['TESTBENCH_APP_BASE_PATH']);
    }
}
