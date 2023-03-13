<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AsCollection_castUsing_class implements CastsAttributes 
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = backport_json_decode($attributes[$key], true);

                return is_array($data) ? new Collection($data) : null;
            }

            public function set(Model $model, $key, $value, array $attributes)
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
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new AsCollection_castUsing_class;
    }
}
