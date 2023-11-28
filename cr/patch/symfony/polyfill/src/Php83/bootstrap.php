<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Polyfill\Php83 as p;

if (!defined('GSLC_SSL_NO_AUTH')) {
    define('GSLC_SSL_NO_AUTH', null);
}

if (\PHP_VERSION_ID >= 80300) {
    return;
}

if (\PHP_VERSION_ID >= 80000) {
    return require __DIR__.'/bootstrap80.php';
}

if (!function_exists('json_validate')) {
    function json_validate(/*string */$json, /*int */$depth = 512, /*int */$flags = 0)/*: bool */{
        $flags = backport_type_check('int', $flags);

        $depth = backport_type_check('int', $depth);

        $json = backport_type_check('string', $json);

        return p\Php83::json_validate($json, $depth, $flags);
    }
}

if (!function_exists('mb_str_pad') && function_exists('mb_substr')) {
    function mb_str_pad(/*string */$string, /*int */$length, /*string */$pad_string = ' ', /*int */$pad_type = STR_PAD_RIGHT, /*?string */$encoding = null)/*: string */{
        $pad_type = backport_type_check('int', $pad_type);

        $pad_string = backport_type_check('string', $pad_string);

        $length = backport_type_check('int', $length);

        $string = backport_type_check('string', $string);

        $encoding = backport_type_check('?string', $encoding);

        return p\Php83::mb_str_pad($string, $length, $pad_string, $pad_type, $encoding);
    }
}

if (!function_exists('stream_context_set_options')) {
    function stream_context_set_options($context, array $options)/*: bool */{ return stream_context_set_option($context, $options); }
}

if (\PHP_VERSION_ID >= 80100) {
    return require __DIR__.'/bootstrap81.php';
}

if (!function_exists('ldap_exop_sync') && function_exists('ldap_exop')) {
    function ldap_exop_sync($ldap, /*string */$request_oid, /*string */$request_data = null, array $controls = null, &$response_data = null, &$response_oid = null)/*: bool */{
        $request_oid = backport_type_check('string', $request_oid);

        $request_data = backport_type_check('string', $request_data);

        return ldap_exop($ldap, $request_oid, $request_data, $controls, $response_data, $response_oid);
    }
}

if (!function_exists('ldap_connect_wallet') && function_exists('ldap_connect')) {
    function ldap_connect_wallet(/*?string */$uri = null, /*string */$wallet, /*string */$password, /*int */$auth_mode = \GSLC_SSL_NO_AUTH) {
        $auth_mode = backport_type_check('int', $auth_mode);

        $password = backport_type_check('string', $password);

        $wallet = backport_type_check('string', $wallet);

        $uri = backport_type_check('?string', $uri);

        return ldap_connect($uri, $wallet, $password, $auth_mode);
    }
}
