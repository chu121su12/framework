<?php

namespace Illuminate\Validation\Rules;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Stringable;
use UnitEnum;

class ArrayRule implements Stringable
{
    /**
     * The accepted keys.
     *
     * @var array
     */
    protected $keys;

    /**
     * Create a new array rule instance.
     *
     * @param  array|null  $keys
     * @return void
     */
    public function __construct($keys = null)
    {
        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $this->keys = is_array($keys) ? $keys : func_get_args();
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->keys)) {
            return 'array';
        }

        $keys = array_map(
            static function ($key) { switch (true) {
                case $key instanceof BackedEnum: return $key->value;
                case $key instanceof UnitEnum: return $key->name;
                default: return $key;
            } },
            $this->keys
        );

        return 'array:'.implode(',', $keys);
    }
}
