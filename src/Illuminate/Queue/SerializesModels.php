<?php

namespace Illuminate\Queue;

use Illuminate\Queue\Attributes\WithoutRelations;
use ReflectionClass;
use ReflectionProperty;

trait SerializesModels
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * Prepare the instance values for serialization.
     *
     * @return array
     */
    public function __serialize()
    {
        $values = [];

        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            if (! version_compare(\PHP_VERSION, '7.4', '>=')) {
                // noop
            }
            elseif (! $property->isInitialized($this)) {
                continue;
            }

            $value = $this->getPropertyValue($property);

            if (method_exists($property, 'hasDefaultValue') && $property->hasDefaultValue() && $value === $property->getDefaultValue()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isPrivate()) {
                $name = "\0{$class}\0{$name}";
            } elseif ($property->isProtected()) {
                $name = "\0*\0{$name}";
            }

            if (method_exists($property, 'getAttributes')) {

            $values[$name] = $this->getSerializedPropertyValue(
                $value,
                empty($property->getAttributes(WithoutRelations::class))
            );

            } else {
                $values[$name] = $this->getSerializedPropertyValue($value);
            }

        }

        return $values;
    }

    /**
     * Restore the model after serialization.
     *
     * @param  array  $values
     * @return void
     */
    public function __unserialize(array $values)
    {
        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isPrivate()) {
                $name = "\0{$class}\0{$name}";
            } elseif ($property->isProtected()) {
                $name = "\0*\0{$name}";
            }

            if (! array_key_exists($name, $values)) {
                continue;
            }

            if (! $property->isPublic()) {
                $property->setAccessible(true);
            }

            $property->setValue(
                $this, $this->getRestoredPropertyValue($values[$name])
            );
        }
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty  $property
     * @return mixed
     */
    protected function getPropertyValue(ReflectionProperty $property)
    {
        if (! $property->isPublic()) {
            $property->setAccessible(true);
        }

        return $property->getValue($this);
    }
}
