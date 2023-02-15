<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class AsStringable_castUsing_class implements CastsAttributes
        {
            public function get(Model $model, $key, $value, array $attributes)
            {
                return isset($value) ? Str::of($value) : null;
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                return isset($value) ? (string) $value : null;
            }
        }

class AsStringable implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Stringable, string|\Stringable>
     */
    public static function castUsing(array $arguments)
    {
        return new AsStringable_castUsing_class;
    }
}
