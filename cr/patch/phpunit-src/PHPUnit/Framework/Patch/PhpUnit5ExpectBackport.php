<?php

namespace PHPUnit\Framework\Patch;

trait PhpUnit5ExpectBackport
{
    public function expectNotToPerformAssertions()
    {
        $this->doesNotPerformAssertions = true;
    }

    public function expectError()
    {
        $this->expectException(\Error::class);
    }

    public function expectErrorMessage($message)
    {
        $this->expectExceptionMessage($message);
    }

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
