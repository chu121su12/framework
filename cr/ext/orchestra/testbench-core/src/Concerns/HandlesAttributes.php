<?php

namespace Orchestra\Testbench\Concerns;

use Closure;
use Illuminate\Support\Collection;

/**
 * @internal
 */
trait HandlesAttributes
{
    protected function attributeRequiresDatabase($database, $version = null)/*: void*/
    {
        $database = is_array($database) ? $database : [$database];

        $driver = $this->getConnection()->getDriverName();

        if (! \in_array($driver, $database, true)) {
            $this->markTestSkipped('Test requires connections of any: '.implode(', ', $database) . '.');
        }

        if ($version !== null && version_compare($this->getConnection()->getServerVersion(), $version, '<')) {
            $this->markTestSkipped('Test requires a '.$driver.' connection >= ' . $version);
        }
    }

    protected function attributeWithConfig($config, $value)/*: void*/
    {
        $this->app['config']->set($config, $value);
    }

    /**
     * Parse test method attributes.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  class-string  $attribute
     */
    protected function parseTestMethodAttributes($app, /*string */$attribute, /*?*/Closure $callback = null)/*: void*/
    {
        $attribute = backport_type_check('string', $attribute);

        $this->resolvePhpUnitAttributes()
            ->lazy()
            ->filter(static function ($attributes, /*string */$key) use ($attribute) {
                $key = backport_type_check('string', $key);

                return $key === $attribute && ! empty($attributes);
            })->flatten()
            ->when(
                \is_null($callback),
                function ($attributes) use ($app) {
                    $attributes->filter(function ($instance) {
                        return \is_string($instance->method) && method_exists($this, $instance->method);
                    })
                        ->each(function ($instance) use ($app) {
                            $this->{$instance->method}($app);
                        });
                },
                static function ($attributes) use ($callback) {
                    $attributes->each($callback);
                }
            );

        if (\method_exists($this, 'attributeBp')) {
            $values = (array) $this->attributeBp();

            if (isset($values['config'])) {
                foreach ($values['config'] as $config) {
                    $app['config']->set(...$config);
                }
            }

            if (isset($values['requires-env'])) {
                foreach ($values['requires-env'] as $env) {
                    if (! env($env)) {
                        $this->markTestSkipped('Required env '.$env.' not configured.');
                    }
                }
            }

            if (isset($values['database'])) {
                $connection = $app['config']->get('database.default');

                $driver = $app['config']->get("database.connections.$connection.driver");

                if (! \in_array($driver, $values['database'], true)) {
                    $this->markTestSkipped('Test requires connections of any: '.implode(', ', $values['database']) . '.');
                }
            }
        }
    }

    /**
     * Resolve PHPUnit method attributes.
     *
     * @phpunit-overrides
     *
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    abstract protected function resolvePhpUnitAttributes()/*: Collection*/;
}
