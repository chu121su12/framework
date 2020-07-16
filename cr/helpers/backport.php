<?php

if (! function_exists('backport_json_decode'))
{
    function backport_json_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        // https://www.php.net/manual/en/function.json-decode 7.0.0 changes
        if ((string) $json === '') {
            return json_decode('-');
        }

        return json_decode($json, $assoc, $depth, $options);
    }
}

if (! function_exists('backport_substr_count'))
{
    function backport_substr_count($haystack, $needle, $offset = 0, $length = null)
    {
        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            if ($offset < 0) {
                $offset = -$offset - 1;
                $haystack = strrev($haystack);
                $needle = strrev($needle);
            }

            if (! is_null($length)) {
                if ($length < 0) {
                    $length = strlen($haystack) + $length - $offset;
                }

                return substr_count($haystack, $needle, $offset, $length);
            } else {
                return substr_count($haystack, $needle, $offset);
            }
        }

        if (! is_null($length)) {
            return substr_count($haystack, $needle, $offset, $length);
        } else {
            return substr_count($haystack, $needle, $offset);
        }
    }
}

if (! function_exists('backport_spaceship_operator'))
{
    function backport_spaceship_operator($left, $right)
    {
        if ($left > $right) {
            return 1;
        }

        if ($left < $right) {
            return -1;
        }

        return 0;
    }
}
