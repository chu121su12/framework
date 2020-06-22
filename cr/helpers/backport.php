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
