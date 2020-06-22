<?php

if (! function_exists('backport_json_decode'))
{
    function backport_json_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $decoded = json_decode($json, $assoc, $depth, $options);

        if (json_last_error() === JSON_ERROR_NONE) {
            // https://www.php.net/manual/en/function.json-decode 7.0.0 changes
            if ((string) $json === '') {
                json_decode('-');
                return null;
            }
        }

        return $decoded;
    }
}
