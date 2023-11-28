<?php

namespace Orchestra\Testbench\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Orchestra\Testbench\Exceptions\ApplicationNotAvailableException;

/**
 * @deprecated
 */
trait WithFactories
{
    /**
     * Load model factories from path.
     *
     * @param  string  $path
     * @return $this
     *
     * @throws \Exception
     */
    protected function withFactories(/*string */$path)
    {
        $path = backport_type_check('string', $path);

        if (\is_null($this->app)) {
            throw ApplicationNotAvailableException::make(__METHOD__);
        }

        return $this->loadFactoriesUsing($this->app, $path);
    }

    /**
     * Load model factories from path using Application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  string  $path
     * @return $this
     *
     * @throws \Exception
     */
    protected function loadFactoriesUsing($app, /*string */$path)
    {
        $path = backport_type_check('string', $path);

        if (! class_exists(ModelFactory::class)) {
            $requirement = <<<'requirement'
Missing `laravel/legacy-factories` in composer.json. Please refer to <https://packages.tools/testbench/troubleshooting.html#class-illuminate-database-eloquent-factory-not-found>
requirement;

            throw new Exception($requirement);
        }

        $app->make(ModelFactory::class)->load($path);

        return $this;
    }
}
