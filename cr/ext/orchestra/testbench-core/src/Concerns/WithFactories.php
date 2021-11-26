<?php

namespace Orchestra\Testbench\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Factory as ModelFactory;

trait WithFactories
{
    /**
     * Load model factories from path.
     *
     * @param  string  $path
     *
     * @return $this
     */
    protected function withFactories(/*string */$path)
    {
        $path = cast_to_string($path);

        return $this->loadFactoriesUsing($this->app, $path);
    }

    /**
     * Load model factories from path using Application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  string  $path
     *
     * @return $this
     */
    protected function loadFactoriesUsing($app, /*string */$path)
    {
        $path = cast_to_string($path);

        if (! class_exists(ModelFactory::class)) {
            $requirement = <<<'requirement'
Missing `laravel/legacy-factories` in composer.json. Please refer to <https://github.com/orchestral/testbench/blob/6.x/README.md#using-legacy-factories>
requirement;

            throw new Exception($requirement);
        }

        $app->make(ModelFactory::class)->load($path);

        return $this;
    }
}
