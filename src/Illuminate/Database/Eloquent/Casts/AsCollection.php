<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class AsCollection_castUsing_class implements CastsAttributes {
            public function get($model, $key, $value, array $attributes)
            {
                return new Collection(json_decode($attributes[$key], true));
            }

            public function set($model, $key, $value, array $attributes)
            {
                return [$key => json_encode($value)];
            }
        }

class AsCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new AsCollection_castUsing_class;
    }
}
