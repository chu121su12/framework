<?php

namespace Illuminate\Tests\Container;

use PHPUnit\Framework\TestCase;

if (\version_compare(\PHP_VERSION, '7.0', '>=')) {
    include_once __DIR__.'/../../tests-stubs/ContextualAttributeBindingTest.php';
}
else
{
    class ContextualAttributeBindingTest extends TestCase {
        public function testNoop()
        {
            $this->assertTrue(true);
        }
    }
}

final class TimezoneObject
{
    public function __construct(
        #[Config('app.timezone')] public readonly ?string $timezone
    ) {
        //
    }
}

final class LocaleObject
{
    public function __construct(
        #[Config('app.locale')] public readonly ?string $locale
    ) {
        //
    }
}
