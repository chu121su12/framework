<?php

namespace PHPUnit\Framework;

trait TestCaseExtraTrait
{
    public function expectExceptionMessageMatches($regularExpression)
    {
        $this->expectedExceptionMessageRegExp = $regularExpression;
    }
}
