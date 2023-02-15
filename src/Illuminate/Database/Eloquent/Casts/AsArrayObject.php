<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AsArrayObject_castUsing_class implements CastsAttributes 
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = backport_json_decode($attributes[$key], true);

                return is_array($data) ? new ArrayObject($data) : null;
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                return [$key => json_encode($value)];
            }

            public function serialize(Model $model, /*string */$key, $value, /*array */$attributes)
            {
                // $key = backport_type_check('string', $key);

                return $value->getArrayCopy();
            }
        }

class AsArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new AsArrayObject_castUsing_class;
    }
}
