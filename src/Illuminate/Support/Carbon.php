<?php

namespace Illuminate\Support;

use Carbon\Carbon as BaseCarbon;
use Carbon\CarbonImmutable as BaseCarbonImmutable;
use Illuminate\Support\Traits\Conditionable;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

class Carbon extends BaseCarbon
{
    use Conditionable;

    /**
     * {@inheritdoc}
     */
    public static function setTestNow($testNow = null)
    {
        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }

    /**
     * Create a Carbon instance from a given ordered UUID or ULID.
     *
     * @param  \Ramsey\Uuid\Uuid|\Symfony\Component\Uid\Ulid|string  $id
     * @return \Illuminate\Support\Carbon
     */
    public static function createFromId($id)
    {
        return Ulid::isValid($id)
            ? static::createFromInterface(Ulid::fromString($id)->getDateTime())
            : static::createFromInterface(Uuid::fromString($id)->getDateTime());
    }

    /**
     * Dump the instance and end the script.
     *
     * @param  mixed  ...$args
     * @return never
     */
    public function dd(...$args)
    {
        dd($this, ...$args);
    }

    /**
     * Dump the instance.
     *
     * @return $this
     */
    public function dump()
    {
        dump($this);

        return $this;
    }
}
