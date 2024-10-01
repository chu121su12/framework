<?php

namespace Illuminate\Tests\Cache;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

if (PHP_VERSION_ID >= 80100) {
    include_once 'Enums.php';
}

class RateLimiterTest extends TestCase
{
    public static function registerNamedRateLimiterDataProvider()/*: array*/
    {
        return [
            // 'uses BackedEnum' => [BackedEnumNamedRateLimiter::API, 'api'],
            // 'uses UnitEnum' => [UnitEnumNamedRateLimiter::THIRD_PARTY, 'THIRD_PARTY'],
            'uses normal string' => ['yolo', 'yolo'],
            'uses int' => [100, '100'],
        ];
    }

    /** @dataProvider registerNamedRateLimiterDataProvider */
    #[DataProvider('registerNamedRateLimiterDataProvider')]
    public function testRegisterNamedRateLimiter(/*mixed */$name, /*string */$expected)/*: void*/
    {
        $reflectedLimitersProperty = new ReflectionProperty(RateLimiter::class, 'limiters');
        $reflectedLimitersProperty->setAccessible(true);

        $rateLimiter = new RateLimiter($this->createMock(Cache::class));
        $rateLimiter->for_($name, function () { return Limit::perMinute(100); });

        $limiters = $reflectedLimitersProperty->getValue($rateLimiter);

        $this->assertArrayHasKey($expected, $limiters);

        $limiterClosure = $rateLimiter->limiter($name);

        $this->assertNotNull($limiterClosure);
    }
}
