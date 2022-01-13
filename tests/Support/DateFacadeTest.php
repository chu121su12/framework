<?php

namespace Illuminate\Tests\Support;

use Carbon\CarbonImmutable;
use Carbon\Factory;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\DateFactory;
use Illuminate\Support\Facades\Date;
use Illuminate\Tests\Support\Fixtures\CustomDateClass;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateFacadeTest extends TestCase
{
    protected function tearDown()/*: void*/
    {
        parent::tearDown();
        DateFactory::use_(Carbon::class);
    }

    protected static function assertBetweenStartAndNow($start, $actual)
    {
        static::assertThat(
            $actual,
            static::logicalAnd(
                static::greaterThanOrEqual($start),
                static::lessThanOrEqual(Carbon::now()->getTimestamp())
            )
        );
    }

    public function testUseClosure()
    {
        $start = Carbon::now()->getTimestamp();
        $this->assertSame(Carbon::class, get_class(Date::now()));
        $this->assertBetweenStartAndNow($start, Date::now()->getTimestamp());
        DateFactory::use_(function (Carbon $date) {
            return new DateTime($date->format('Y-m-d H:i:s.u'), $date->getTimezone());
        });
        $start = Carbon::now()->getTimestamp();
        $this->assertSame(DateTime::class, get_class(Date::now()));
        $this->assertBetweenStartAndNow($start, Date::now()->getTimestamp());
    }

    public function testUseClassName()
    {
        $start = Carbon::now()->getTimestamp();
        $this->assertSame(Carbon::class, get_class(Date::now()));
        $this->assertBetweenStartAndNow($start, Date::now()->getTimestamp());
        DateFactory::use_(DateTime::class);
        $start = Carbon::now()->getTimestamp();
        $this->assertSame(DateTime::class, get_class(Date::now()));
        $this->assertBetweenStartAndNow($start, Date::now()->getTimestamp());
    }

    public function testCarbonImmutable()
    {
        DateFactory::use_(CarbonImmutable::class);
        $this->assertSame(CarbonImmutable::class, get_class(Date::now()));
        DateFactory::use_(Carbon::class);
        $this->assertSame(Carbon::class, get_class(Date::now()));
        DateFactory::use_(function (Carbon $date) {
            return $date->toImmutable();
        });
        $this->assertSame(CarbonImmutable::class, get_class(Date::now()));
        DateFactory::use_(function ($date) {
            return $date;
        });
        $this->assertSame(Carbon::class, get_class(Date::now()));

        DateFactory::use_(new Factory([
            'locale' => 'fr',
        ]));
        $this->assertSame('fr', Date::now()->locale);
        DateFactory::use_(Carbon::class);
        $this->assertSame('en', Date::now()->locale);
        DateFactory::use_(CustomDateClass::class);
        $this->assertInstanceOf(CustomDateClass::class, Date::now());
        $this->assertInstanceOf(Carbon::class, Date::now()->getOriginal());
        DateFactory::use_(Carbon::class);
    }

    public function testUseInvalidHandler()
    {
        $this->expectException(InvalidArgumentException::class);

        DateFactory::use_(42);
    }

    public function testMacro()
    {
        Date::macro('returnNonDate', function () {
            return 'string';
        });

        $this->assertSame('string', Date::returnNonDate());
    }
}
