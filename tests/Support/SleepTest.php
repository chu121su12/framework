<?php

namespace Illuminate\Tests\Support;

use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SleepTest extends TestCase
{
    use \PHPUnit\Framework\PhpUnit8Assert;

    protected function tearDown()/*: void*/
    {
        parent::tearDown();

        Sleep::fake(false);

        Carbon::setTestNow();
    }

    public function testItSleepsForSeconds()
    {
        $start = microtime(true);
        Sleep::for_(1)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(1, $end - $start, 0.03);
    }

    public function testItSleepsForSecondsWithMilliseconds()
    {
        $start = microtime(true);
        Sleep::for_(1.5)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(1.5, $end - $start, 0.03);
    }

    public function testItCanFakeSleeping()
    {
        Sleep::fake();

        $start = microtime(true);
        Sleep::for_(1.5)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(0, $end - $start, 0.03);
    }

    public function testItCanSpecifyMinutes()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1.5)->minutes();

        $this->assertSame($sleep->duration->totalMicroseconds, 90000000);
    }

    public function testItCanSpecifyMinute()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1)->minute();

        $this->assertSame($sleep->duration->totalMicroseconds, 60000000);
    }

    public function testItCanSpecifySeconds()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1.5)->seconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1500000);
    }

    public function testItCanSpecifySecond()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1)->second();

        $this->assertSame($sleep->duration->totalMicroseconds, 1000000);
    }

    public function testItCanSpecifyMilliseconds()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1.5)->milliseconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1500);
    }

    public function testItCanSpecifyMillisecond()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1)->millisecond();

        $this->assertSame($sleep->duration->totalMicroseconds, 1000);
    }

    public function testItCanSpecifyMicroseconds()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1.5)->microseconds();

        // rounded as microseconds is the smallest unit supported...
        $this->assertSame($sleep->duration->totalMicroseconds, 1);
    }

    public function testItCanSpecifyMicrosecond()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1)->microsecond();

        $this->assertSame($sleep->duration->totalMicroseconds, 1);
    }

    public function testItCanChainDurations()
    {
        Sleep::fake();

        $sleep = Sleep::for_(1)->second()
                      ->and_(500)->microseconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1000500);
    }

    public function testItCanUseDateInterval()
    {
        Sleep::fake();

        $sleep = Sleep::for_(CarbonInterval::seconds(1)->addMilliseconds(5));

        $this->assertSame($sleep->duration->totalMicroseconds, 1005000);
    }

    public function testItThrowsForUnknownTimeUnit()
    {
        try {
            Sleep::for_(5);
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertSame('Unknown duration unit.', $e->getMessage());
        }
    }

    public function testItCanAssertSequence()
    {
        Sleep::fake();

        Sleep::for_(5)->seconds();
        Sleep::for_(1)->seconds()->and_(5)->microsecond();

        Sleep::assertSequence([
            Sleep::for_(5)->seconds(),
            Sleep::for_(1)->seconds()->and_(5)->microsecond(),
        ]);
    }

    public function testItFailsSequenceAssertion()
    {
        Sleep::fake();

        Sleep::for_(5)->seconds();
        Sleep::for_(1)->seconds()->and_(5)->microseconds();

        try {
            Sleep::assertSequence([
                Sleep::for_(5)->seconds(),
                Sleep::for_(9)->seconds()->and_(8)->milliseconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected sleep duration of [9 seconds 8 milliseconds] but actually slept for [1 second 5 microseconds].\nFailed asserting that false is true.", $e->getMessage());
        }
    }

    public function testItCanUseSleep()
    {
        Sleep::fake();

        Sleep::sleep(3);

        Sleep::assertSequence([
            Sleep::for_(3)->seconds(),
        ]);
    }

    public function testItCanUseUSleep()
    {
        Sleep::fake();

        Sleep::usleep(3);

        Sleep::assertSequence([
            Sleep::for_(3)->microseconds(),
        ]);
    }

    public function testItCanSleepTillGivenTime()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->addMinute());

        Sleep::assertSequence([
            Sleep::for_(60)->seconds(),
        ]);
    }

    public function testItCanSleepTillGivenTimestamp()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->addMinute()->timestamp);

        Sleep::assertSequence([
            Sleep::for_(60)->seconds(),
        ]);
    }

    public function testItSleepsForZeroTimeWithNegativeDateTime()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->subMinutes(100));

        Sleep::assertSequence([
            Sleep::for_(0)->seconds(),
        ]);
    }

    public function testSleepingForZeroTime()
    {
        Sleep::fake();

        Sleep::for_(0)->seconds();

        try {
            Sleep::assertSequence([
                Sleep::for_(1)->seconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected sleep duration of [1 second] but actually slept for [0 microseconds].\nFailed asserting that false is true.", $e->getMessage());
        }
    }

    public function testItFailsWhenSequenceContainsTooManySleeps()
    {
        Sleep::fake();

        Sleep::for_(1)->seconds();

        try {
            Sleep::assertSequence([
                Sleep::for_(1)->seconds(),
                Sleep::for_(1)->seconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testSilentlySetsDurationToZeroForNegativeValues()
    {
        Sleep::fake();

        Sleep::for_(-1)->seconds();

        Sleep::assertSequence([
            Sleep::for_(0)->seconds(),
        ]);
    }

    public function testItDoesntCaptureAssertionInstances()
    {
        Sleep::fake();

        Sleep::for_(1)->second();

        Sleep::assertSequence([
            Sleep::for_(1)->second(),
        ]);

        try {
            Sleep::assertSequence([
                Sleep::for_(1)->second(),
                Sleep::for_(1)->second(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testAssertNeverSlept()
    {
        Sleep::fake();

        Sleep::assertNeverSlept();

        Sleep::for_(1)->seconds();

        try {
            Sleep::assertNeverSlept();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }
    }

    public function testAssertNeverAgainstZeroSecondSleep()
    {
        Sleep::fake();

        Sleep::assertNeverSlept();

        Sleep::for_(0)->seconds();

        try {
            Sleep::assertNeverSlept();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }
    }

    public function testItCanAssertNoSleepingOccurred()
    {
        Sleep::fake();

        Sleep::assertInsomniac();

        Sleep::for_(0)->second();

        // we still have not slept...
        Sleep::assertInsomniac();

        Sleep::for_(1)->second();

        try {
            Sleep::assertInsomniac();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Unexpected sleep duration of [1 second] found.\nFailed asserting that 1000000 is identical to 0.", $e->getMessage());
        }
    }

    public function testItCanAssertSleepCount()
    {
        Sleep::fake();

        Sleep::assertSleptTimes(0);

        Sleep::for_(1)->second();

        Sleep::assertSleptTimes(1);

        try {
            Sleep::assertSleptTimes(0);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }

        try {
            Sleep::assertSleptTimes(2);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testAssertSlept()
    {
        Sleep::fake();

        Sleep::assertSlept(function () { return true; }, 0);

        try {
            Sleep::assertSlept(function () { return true; });
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [0] times instead of [1].\nFailed asserting that 0 is identical to 1.", $e->getMessage());
        }

        Sleep::for_(5)->seconds();

        Sleep::assertSlept(function (CarbonInterval $duration) { return $duration->totalSeconds === 5; });

        try {
            Sleep::assertSlept(function (CarbonInterval $duration) { return $duration->totalSeconds === 5; }, 2);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [1] times instead of [2].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }

        try {
            Sleep::assertSlept(function (CarbonInterval $duration) { return $duration->totalSeconds === 6; });
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [0] times instead of [1].\nFailed asserting that 0 is identical to 1.", $e->getMessage());
        }
    }
}
