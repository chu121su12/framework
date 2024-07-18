<?php

namespace Illuminate\Hashing;

abstract class AbstractHasher
{
    /**
     * Get information about the given hashed value.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        $info = password_get_info($hashedValue);

        if (version_compare(PHP_VERSION, '8.2.0', '<')
            && $info['algoName'] === 'unknown'
            && $info['algo'] === 0
            && $info['options'] === []) {
            return \array_merge($info, [
                'algo' => null,
            ]);
        }

        return $info;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string|null  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check(#[\SensitiveParameter] $value, $hashedValue, array $options = [])
    {
        if (is_null($hashedValue) || strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }
}
