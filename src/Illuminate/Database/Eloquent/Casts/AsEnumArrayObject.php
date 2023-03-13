<?php

namespace Illuminate\Database\Eloquent\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AsEnumArrayObject_castUsing_class implements CastsAttributes
        {
            protected $arguments;

            public function __construct(array $arguments)
            {
                $this->arguments = $arguments;
            }

            public function get(Model $model, $key, $value, array $attributes)
            {
                if (! isset($attributes[$key]) || is_null($attributes[$key])) {
                    return;
                }

                $data = json_decode($attributes[$key], true);

                if (! is_array($data)) {
                    return;
                }

                $enumClass = $this->arguments[0];

                return new ArrayObject((new Collection($data))->map(function ($value) use ($enumClass) {
                    return is_subclass_of($enumClass, BackedEnum::class)
                        ? $enumClass::from($value)
                        : constant($enumClass.'::'.$value);
                })->toArray());
            }

            public function set(Model $model, $key, $value, array $attributes)
            {
                if ($value === null) {
                    return [$key => null];
                }

                $storable = [];

                foreach ($value as $enum) {
                    $storable[] = $this->getStorableEnumValue($enum);
                }

                return [$key => json_encode($storable)];
            }

            public function serialize(Model $model, /*string */$key, $value, /*array */$attributes)
            {
                // $key = backport_type_check('string', $key);

                return (new Collection($value->getArrayCopy()))->map(function ($enum) {
                    return $this->getStorableEnumValue($enum);
                })->toArray();
            }

            protected function getStorableEnumValue($enum)
            {
                if (is_string($enum) || is_int($enum)) {
                    return $enum;
                }

                return $enum instanceof BackedEnum ? $enum->value : $enum->name;
            }
        }

class AsEnumArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @template TEnum
     *
     * @param  array{class-string<TEnum>}  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, TEnum>, iterable<TEnum>>
     */
    public static function castUsing(array $arguments)
    {
        return new AsEnumArrayObject_castUsing_class($arguments);
    }
}
