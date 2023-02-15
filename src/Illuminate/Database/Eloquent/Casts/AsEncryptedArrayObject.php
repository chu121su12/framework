<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class AsEncryptedArrayObject_castUsing_class implements CastsAttributes 
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                if (isset($attributes[$key])) {
                    return new ArrayObject(backport_json_decode(Crypt::decryptString($attributes[$key]), true));
                }

                return null;
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                if (! is_null($value)) {
                    return [$key => Crypt::encryptString(json_encode($value))];
                }

                return null;
            }

            public function serialize(Model $model, /*string */$key, $value, /*array */$attributes)
            {
                // $key = backport_type_check('string', $key);

                return ! is_null($value) ? $value->getArrayCopy() : null;
            }
        }

class AsEncryptedArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new AsEncryptedArrayObject_castUsing_class;
    }
}
