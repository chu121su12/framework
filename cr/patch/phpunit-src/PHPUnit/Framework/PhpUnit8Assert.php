<?php

namespace PHPUnit\Framework;

if (phpunit_major_version() < 8) {
    trait PhpUnit8Assert
    {
        use Patch\ExtraAssertions;
        use Patch\LaravelAssertBackport;
        use Patch\PhpUnit5AssertBackport;
        use Patch\PhpUnit7AssertBackport;
    }

} else {
    trait PhpUnit8Assert
    {
        use Patch\ExtraAssertions;
        use Patch\LaravelAssertBackport;
    }
}
