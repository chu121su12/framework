<?php

namespace Spatie\Ignition\Solutions\OpenAi;

use Psr\SimpleCache\CacheInterface;

class DummyCache implements CacheInterface
{
    public function get(/*string */$key, /*mixed */$default = null)/*: mixed*/
    {
        $key = backport_type_check('string', $key);
        $default = backport_type_check('mixed', $default);

        return null;
    }

    public function set(/*string */$key, /*mixed */$value, /*\DateInterval|int|null */$ttl = null)/*: bool*/
    {
        $key = backport_type_check('string', $key);
        $value = backport_type_check('mixed', $value);
        $ttl = backport_type_check('\DateInterval|int|null', $ttl);

        return true;
    }

    public function delete(/*string */$key)/*: bool*/
    {
        $key = backport_type_check('string', $key);

        return true;
    }

    public function clear()/*: bool*/
    {
        return true;
    }

    public function getMultiple(/*iterable */$keys, /*mixed */$default = null)/*: iterable*/
    {
        $keys = backport_type_check('iterable', $keys);
        $default = backport_type_check('mixed', $default);

        return [];
    }

    public function setMultiple(/*iterable */$values, /*\DateInterval|int|null */$ttl = null)/*: bool*/
    {
        $values = backport_type_check('iterable', $values);
        $ttl = backport_type_check('\DateInterval|int|null', $ttl);

        return true;
    }

    public function deleteMultiple(/*iterable */$keys)/*: bool*/
    {
        $keys = backport_type_check('iterable', $keys);

        return true;
    }

    public function has(/*string */$key)/*: bool*/
    {
        $key = backport_type_check('string', $key);

        return false;
    }
}
