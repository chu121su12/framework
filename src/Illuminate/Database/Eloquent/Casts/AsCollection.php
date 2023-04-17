<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AsCollection_castUsing_class implements CastsAttributes 
        {
            protected /*array */$arguments;

            public function __construct(/*protected */array $arguments)
            {
                $this->arguments = $arguments;
            }

            public function get(Model $model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = Json::decode($attributes[$key]);

                $collectionClass = isset($this->arguments[0]) ? $this->arguments[0] : Collection::class;

                if (! is_a($collectionClass, Collection::class, true)) {
                    throw new InvalidArgumentException('The provided class must extend ['.Collection::class.'].');
                }

                return is_array($data) ? new $collectionClass($data) : null;
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                return [$key => Json::encode($value)];
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
        return new AsCollection_castUsing_class($arguments);
    }
}
