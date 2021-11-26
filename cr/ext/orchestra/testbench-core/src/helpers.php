<?php

namespace Orchestra\Testbench;

use Illuminate\Testing\PendingCommand;

class function_container_class {
        use Concerns\CreatesApplication;
    }

/**
 * Create Laravel application instance.
 *
 * @return object
 */
function container()
{
    return new function_container_class;
}

/**
 * Run artisan command.
 *
 * @param  \Orchestra\Testbench\Contracts\TestCase  $testbench
 * @param  string  $command
 * @param  array  $parameters
 *
 * @return \Illuminate\Testing\PendingCommand|int
 */
function artisan(Contracts\TestCase $testbench, /*string */$command, array $parameters = [])
{
    $command = cast_to_string($command);

    return tap($testbench->artisan($command, $parameters), function ($artisan) {
        if ($artisan instanceof PendingCommand) {
            $artisan->run();
        }
    });
}