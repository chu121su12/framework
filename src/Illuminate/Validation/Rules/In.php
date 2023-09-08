<?php

namespace Illuminate\Validation\Rules;

use BackedEnum;
use Stringable;
use UnitEnum;

class In implements Stringable
{
    /**
     * The name of the rule.
     *
     * @var string
     */
    protected $rule = 'in';

    /**
     * The accepted values.
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new in rule instance.
     *
     * @param  array  $values
     * @return void
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     *
     * @see \Illuminate\Validation\ValidationRuleParser::parseParameters
     */
    public function __toString()
    {
        $values = array_map(function ($value) {
            switch (true) {
                case $value instanceof BackedEnum: $value = $value->value; break;
                case $value instanceof UnitEnum: $value = $value->name; break;
                default: $value = $value; break;
            };

            return '"'.str_replace('"', '""', $value).'"';
        }, $this->values);

        return $this->rule.':'.implode(',', $values);
    }
}
