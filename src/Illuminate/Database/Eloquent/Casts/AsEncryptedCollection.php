<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class AsEncryptedCollection_castUsing_class implements CastsAttributes 
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                if (isset($attributes[$key])) {
                    return new Collection(backport_json_decode(Crypt::decryptString($attributes[$key]), true));
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
        }

class AsEncryptedCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new AsEncryptedCollection_castUsing_class;
    }
}
