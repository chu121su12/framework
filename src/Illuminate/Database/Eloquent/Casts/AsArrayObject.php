<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AsArrayObject_castUsing_class implements CastsAttributes 
        {
            public function get($model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = backport_json_decode($attributes[$key], true);

                return is_array($data) ? new ArrayObject($data) : null;
            }

            public function set($model, $key, $value, array $attributes)
            {
                return [$key => json_encode($value)];
            }

            public function serialize($model, /*string */$key, $value, /*array */$attributes)
            {
                // $key = cast_to_string($key);

                return $value->getArrayCopy();
            }
        }

class AsArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new AsArrayObject_castUsing_class;
    }
}
