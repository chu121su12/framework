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
        try {
            parent::expectExceptionCode($code);
        } catch (\PHPUnit_Framework_Exception $e) {
            if (!(is_null($code) && $e->getMessage() === 'Argument #1 (No Value) of PHPUnit_Framework_TestCase::expectExceptionCode() must be a integer or string"')) {
                throw $e;
            }
        }
    }
}
