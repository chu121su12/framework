<?php

namespace PHPUnit\Framework;

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
