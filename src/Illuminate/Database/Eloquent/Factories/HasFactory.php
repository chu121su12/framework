<?php

namespace Illuminate\Database\Eloquent\Factories;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 */
trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  (callable(array<string, mixed>, static|null): array<string, mixed>)|array<string, mixed>|int|null  $count
     * @param  (callable(array<string, mixed>, static|null): array<string, mixed>)|array<string, mixed>  $state
     * @return TFactory
     */
    public static function factory($count = null, $state = [])
    {
        $factory = static::newFactory();

        $factory = isset($factory) ? $factory : Factory::factoryForModel(static::class);

        return $factory
                    ->count(backport_is_numeric($count) ? $count : null)
                    ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return TFactory|null
     */
    protected static function newFactory()
    {
        if (isset(static::$factory)) {
            $factory = static::$factory;

            return $factory::new_();
        }

        return null;
    }
}
