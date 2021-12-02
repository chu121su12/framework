<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class AsEncryptedArrayObject_castUsing_class implements CastsAttributes 
        {
            public function get($model, $key, $value, array $attributes)
            {
                if (isset($attributes[$key])) {
                    return new ArrayObject(backport_json_decode(Crypt::decryptString($attributes[$key]), true));
                }

                return null;
            }

            public function set($model, $key, $value, array $attributes)
            {
                if (! is_null($value)) {
                    return [$key => Crypt::encryptString(json_encode($value))];
                }

                return null;
            }

            public function serialize($model, /*string */$key, $value, /*array */$attributes)
            {
                // $key = cast_to_string($key);

                return ! is_null($value) ? $value->getArrayCopy() : null;
            }
        }

class AsEncryptedArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new AsEncryptedArrayObject_castUsing_class;
    }
}
