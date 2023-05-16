<?php

namespace Carbon\Patch;

if (PHP_VERSION_ID < 80000) {
    trait DateTimeImmutableCreateFromInterfacePolyfill {
        public static function createFromInterface(\DateTimeInterface $object) {
            if ($object instanceof \DateTime) {
                // return \DateTimeImmutable::createFromMutable($object);
                return new \Carbon\Carbon($object);
            }
            if ($object instanceof \DateTimeImmutable) {
                // return clone $object;
                return new \Carbon\CarbonImmutable($object);
            }
            throw new \InvalidArgumentException('Unexpected type');
        }
    }
}
else
{
    trait DateTimeImmutableCreateFromInterfacePolyfill {}
}
