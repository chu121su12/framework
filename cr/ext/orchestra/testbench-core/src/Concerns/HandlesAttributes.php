<?php

namespace Orchestra\Testbench\Concerns;

use Closure;
use Illuminate\Support\Collection;

/**
 * @internal
 */
trait HandlesAttributes
{
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
