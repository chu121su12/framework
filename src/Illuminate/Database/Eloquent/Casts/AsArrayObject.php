<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsArrayObject_castUsing_class implements CastsAttributes 
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = Json::decode($attributes[$key]);

                return is_array($data) ? new ArrayObject($data) : null;
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                return [$key => Json::encode($value)];
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
