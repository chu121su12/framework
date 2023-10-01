<?php

namespace Illuminate\Tests\Cr;

use Illuminate\Support\Carbon;
use Illuminate\Support\Sleep;
use Orchestra\Testbench\TestCase;

class ExtraTest extends TestCase
{
    public function testCarbonUpdatedInFakedSleep()
    {
        Carbon::setTestNow(now()->startOfMinute());
        Sleep::fake();
        Sleep::whenFakingSleep(function ($duration) {
            return Carbon::setTestNow(now()->add($duration));
        });
        Sleep::usleep(1234000);
        $this->assertEquals(Carbon::now()->format('s:u'), '01:234000');
    }
}
