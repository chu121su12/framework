<?php

namespace PHPUnit\Framework;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use PhpUnit8Expect;

    /**
     * @param null|int|string $code
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function expectExceptionCode($code)
    {
        if (!$this->expectedException) {
            $this->expectedException = \Exception::class;
        }

        if (!is_null($code) && !is_int($code) && !is_string($code)) {
            throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'null, integer or string');
        }

        $this->expectedExceptionCode = $code;
    }

    // protected function runTest()
    // {
    //     try {
    //         return parent::runTest();
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }
}
