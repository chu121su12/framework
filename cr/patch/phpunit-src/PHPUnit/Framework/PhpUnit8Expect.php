<?php

namespace PHPUnit\Framework;

if (phpunit_major_version() < 8) {
    trait PhpUnit8Expect
    {
        use Patch\PhpUnit5ExpectBackport;
    }
}
