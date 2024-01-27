<?php

namespace Illuminate\Support;

use Carbon\Carbon as BaseCarbon;
use Carbon\CarbonImmutable as BaseCarbonImmutable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Dumpable;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

class Carbon extends BaseCarbon
{
    use Conditionable, Dumpable;

    /**
     * {@inheritdoc}
     */
    public static function setTestNow(/*mixed */$testNow = null)/*: void*/
    {
        $testNow = backport_type_check('mixed', $testNow);

        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }

    /**
     * Create a Carbon instance from a given ordered UUID or ULID.
     */
    public static function createFromId(/*Uuid|Ulid|string */$id)/*: static*/
    {
        $id = backport_type_check([
            Uuid::class,
            Ulid::class,
            'string',
        ], $id);

        if (is_string($id)) {
            $id = Ulid::isValid($id) ? Ulid::fromString($id) : Uuid::fromString($id);
        }

        return static::createFromInterface($id->getDateTime());
    }
}
