<?php

namespace Illuminate\Hashing;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Manager;

/**
 * @mixin \Illuminate\Contracts\Hashing\Hasher
 */
class HashManager extends Manager implements Hasher
{
    /**
     * Create an instance of the Bcrypt hash Driver.
     *
     * @return \Illuminate\Hashing\BcryptHasher
     */
    public function createBcryptDriver()
    {
        $config = $this->config->get('hashing.bcrypt');

        return new BcryptHasher(isset($config) ? $config : []);
    }

    /**
     * Create an instance of the Argon2i hash Driver.
     *
     * @return \Illuminate\Hashing\ArgonHasher
     */
    public function createArgonDriver()
    {
        $config = $this->config->get('hashing.argon');

        return new ArgonHasher(isset($config) ? $config : []);
    }

    /**
     * Create an instance of the Argon2id hash Driver.
     *
     * @return \Illuminate\Hashing\Argon2IdHasher
     */
    public function createArgon2idDriver()
    {
        $config = $this->config->get('hashing.argon');

        return new Argon2IdHasher(isset($config) ? $config : []);
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return $this->driver()->info($hashedValue);
    }

    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        return $this->driver()->make($value, $options);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        return $this->driver()->check($value, $hashedValue, $options);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return $this->driver()->needsRehash($hashedValue, $options);
    }

    /**
     * Determine if a given string is already hashed.
     *
     * @param  string  $value
     * @return bool
     */
    public function isHashed($value)
    {
        $info = password_get_info($value);

        if ($info['algo'] === null) {
            return false;
        }

        if (version_compare(PHP_VERSION, '8.2.0', '<')
            && $info['algoName'] === 'unknown'
            && $info['algo'] === 0
            && $info['options'] === []) {
            return false;
        }

        return true;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('hashing.driver', 'bcrypt');
    }
}
