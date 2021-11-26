<?php

namespace Orchestra\Testbench\Concerns\Database;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesConnections
{
    /**
     * Allow to use database connections environment variables.
     */
    final protected function usesDatabaseConnectionsEnvironmentVariables(Repository $config, /*string */$driver, /*string */$keyword)////: void
    {
        $driver = cast_to_string($driver);

        $keyword = cast_to_string($keyword);

        $keyword = Str::upper($keyword);

        $configurations = [];
        $options = [
            'url' => 'URL',
            'host' => 'HOST',
            'port' => 'PORT',
            'database' => ['DB', 'DATABASE'],
            'username' => ['USER', 'USERNAME'],
            'password' => 'PASSWORD',
        ];

        foreach ($options as $key => $value) {
            $collection = Collection::make(
                Arr::wrap($value)
            )->transform(static function ($value) use ($keyword) {
                return env("{$keyword}_{$value}");
            })->first(static function ($value) {
                return ! \is_null($value);
            });

            $configurations["database.connections.{$driver}.{$key}"] = isset($collection) ? $collection : $config->get("database.connections.{$driver}.{$key}");
        }

        $config->set($configurations);
    }
}
