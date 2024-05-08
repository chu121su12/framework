<?php

namespace Laravel\Octane\Commands\Concerns;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Parser\Parser;
use Dotenv\Store\StoreBuilder;
use Illuminate\Support\Env;

trait InteractsWithEnvironmentVariables
{
    /**
     * Forgets the current process environment variables.
     *
     * @return void
     */
    public function forgetEnvironmentVariables()
    {
        $variables = collect();

        try {
            $content = Dotenv::create(
                Env::getRepository(),
                app()->environmentPath(),
                app()->environmentFile()
            );
        } catch (InvalidPathException $e) {
            // ..
        }

        $variables->each(function ($name) { return Env::getRepository()->clear($name); });
    }
}
