<?php

namespace Illuminate\Database\Eloquent\Factories;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  mixed  $parameters
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function factory(...$parameters)
    {
        return Factory::factoryForModel(get_called_class())
                    ->count(is_numeric(isset($parameters[0]) ? $parameters[0] : null) ? $parameters[0] : 1)
                    ->state(is_array(isset($parameters[0]) ? $parameters[0] : null) ? $parameters[0] : (isset($parameters[1]) ? $parameters[1] : []));
    }
}
