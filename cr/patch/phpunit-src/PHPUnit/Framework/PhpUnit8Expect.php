<?php

namespace PHPUnit\Framework;

if (phpunit_major_version() > 5) {
    trait PhpUnit8Expect
    {
    }

} else {
    trait PhpUnit8Expect
    {
        public function expectExceptionMessageMatches($regularExpression)
        {
            $this->expectedExceptionMessageRegExp = $regularExpression;
        }

        public function expectExceptionObject($exception)
        {
            $this->expectException(\get_class($exception));
            $this->expectExceptionMessage($exception->getMessage());
            $this->expectExceptionCode($exception->getCode());
        }

    }
}
