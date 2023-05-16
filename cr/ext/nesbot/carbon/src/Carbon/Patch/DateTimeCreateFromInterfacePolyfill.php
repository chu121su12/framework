<?php

namespace Carbon\Patch;

if (PHP_VERSION_ID < 80000) {
    trait DateTimeCreateFromInterfacePolyfill {
        public static function createFromInterface(\DateTimeInterface $object) {
            if ($object instanceof \DateTimeImmutable) {
                // return \DateTime::createFromImmutable($object);
                return new \Carbon\CarbonImmutable($object);
            }
            if ($object instanceof \DateTime) {
                // return clone $object;
                return new \Carbon\Carbon($object);
            }
            throw new \InvalidArgumentException('Unexpected type');
        }
    }
}
else
{
    trait DateTimeCreateFromInterfacePolyfill {}
}
