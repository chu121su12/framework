<?php

use Illuminate\Support\Str;

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @template TValue
     *
     * @param  TValue  $value
     * @param  (callable(TValue): TValue)|null  $callback
     * @return TValue
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! \function_exists('_h_arr_get')) {
    function _h_arr_get(array $array, $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (isset($array[$segment])) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (! \function_exists('_data_get')) {
    function _data_get($object, $where, $defaultValue = null) {
        if (\is_object($object)) {
            if (isset($object->{$where})) {
                return $object->{$where};
            }
        }

        if (\is_array($object)) {
            if (isset($object[$where])) {
                return $object[$where];
            }
        }

        return $defaultValue;
    }
}

if (! \function_exists('_reflection_call_inaccessible_method')) {
    function _reflection_call_inaccessible_method($instance, $method, ...$args)
    {
        $r = new \ReflectionMethod($instance, $method);
        $r->setAccessible(true);
        return $r->invoke($instance, ...$args);
    }
}

if (! \function_exists('_check_db_connection_versions')) {
    function _check_db_connection_versions($connection, $driver, $operator = null, $version = null)
    {
        if (func_num_args() === 3 || (! is_string($driver) && func_num_args() === 4)) {
            throw new InvalidArgumentException('Illegal arguments combination.');
        }

        if ($connection instanceof Mockery\LegacyMockInterface) {
            return true;
        }

        $actualDriver = $connection->getDriverName();
        if (! isset($actualDriver)) {
            $actualDriver = $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
        $actualVersion = $connection->getConfig('version');
        if (! isset($actualVersion)) {
            $actualVersion = $connection->getServerVersion();
        }

        switch (true) {
            case Str::contains($actualVersion, 'MariaDB'):
                list($actualDriver, $actualVersion) = ['mariadb', Str::between($actualVersion, '5.5.5-', '-MariaDB')];
                break;

            case Str::contains($actualVersion, ['vitess', 'PlanetScale']):
                list($actualDriver, $actualVersion) = ['vitess', Str::before($actualVersion, '-')];
                break;

            default:
                list($actualDriver, $actualVersion) = [strtolower($actualDriver), $actualVersion];
        }

        if ($driver instanceof Closure) {
            return $driver($actualDriver, $actualVersion);
        }

        if (is_array($driver)) {
            foreach ($driver as $key => $value) {
                switch (true) {
                    case is_string($key):
                        list($name, $operator, $version) = [$key, $value[0], $value[1]];
                        break;

                    case is_array($value):
                        list($name, $operator, $version) = $value;
                        break;

                    default:
                        list($name, $operator, $version) = [$value, null, null];
                }

                if (strtolower($name) === $actualDriver
                    && (! $operator || ! $version || version_compare($actualVersion, $version, $operator))) {
                    return true;
                }
            }

            return false;
        }

        return strtolower($driver) === $actualDriver
            && (! $operator || ! $version || version_compare($actualVersion, $version, $operator));
    }
}