<?php

namespace Illuminate\Database\Eloquent\Casts;

class Json
{
    /**
     * The custom JSON encoder.
     *
     * @var callable|null
     */
    protected static $encoder;

    /**
     * The custom JSON decode.
     *
     * @var callable|null
     */
    protected static $decoder;

    /**
     * Encode the given value.
     */
    public static function encode(/*mixed */$value)/*: mixed*/
    {
        $value = backport_type_check('mixed', $value);

        return isset(static::$encoder) ? call_user_func(static::$encoder, $value) : json_encode($value);
    }

    /**
     * Decode the given value.
     */
    public static function decode(/*mixed */$value, /*?bool */$associative = true)/*: mixed*/
    {
        $value = backport_type_check('mixed', $value);

        $associative = backport_type_check('?bool', $associative);

        return isset(static::$decoder)
                ? call_user_func(static::$decoder, $value, $associative)
                : backport_json_decode($value, $associative);
    }

    /**
     * Encode all values using the given callable.
     */
    public static function encodeUsing(/*?*/callable $encoder = null)/*: void*/
    {
        $encoder = backport_type_check('?callable', $encoder);

        static::$encoder = $encoder;
    }

    /**
     * Decode all values using the given callable.
     */
    public static function decodeUsing(/*?*/callable $decoder = null)/*: void*/
    {
        $encoder = backport_type_check('?callable', $encoder);

        static::$decoder = $decoder;
    }
}
