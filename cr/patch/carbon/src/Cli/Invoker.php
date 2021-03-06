<?php

namespace Carbon\Cli;

class Invoker
{
    const CLI_CLASS_NAME = 'Carbon\\Cli';

    protected function runWithCli($className, array $parameters) //// bool
    {
        $className = cast_to_string($className);

        $cli = new $className();

        return $cli(...$parameters);
    }

    public function __invoke(...$parameters) //// bool
    {
        if (class_exists(self::CLI_CLASS_NAME)) {
            return $this->runWithCli(self::CLI_CLASS_NAME, $parameters);
        }

        $function = ((isset($parameters[1]) ? $parameters[1] : '') === 'install' ? (isset($parameters[2]) ? $parameters[2] : null) : null) ?: 'shell_exec';
        $function('composer require carbon-cli/carbon-cli --no-interaction');

        echo 'Installation succeeded.';

        return true;
    }
}
