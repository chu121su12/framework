<?php

namespace PHPUnit\Framework;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_Test, \PHPUnit_Framework_SelfDescribing
{
    use PhpUnit8Expect;
}
