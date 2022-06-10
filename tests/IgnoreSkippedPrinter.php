<?php

namespace Illuminate\Tests;

use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\DefaultResultPrinter as PHPUnit9ResultPrinter;
use PHPUnit\TextUI\ResultPrinter as PHPUnit8ResultPrinter;

if (phpunit_major_version() >= 9) {
    class IgnoreSkippedPrinter extends PHPUnit9ResultPrinter
    {
        protected function printSkipped(TestResult $result)/*: void*/
        {
            //
        }
    }
} else {
    class IgnoreSkippedPrinter extends \PHPUnit_TextUI_ResultPrinter
    {
        protected function printSkipped(\PHPUnit_Framework_TestResult $result)/*: void*/
        {
            //
        }
    }
}
