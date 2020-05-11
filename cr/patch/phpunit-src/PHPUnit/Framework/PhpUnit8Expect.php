<?php

namespace PHPUnit\Framework;

trait PhpUnit8Expect
{
    public function expectExceptionMessageMatches($regularExpression)
    {
        $this->expectedExceptionMessageRegExp = $regularExpression;
    }
}
