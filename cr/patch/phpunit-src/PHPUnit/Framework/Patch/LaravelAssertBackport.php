<?php

namespace PHPUnit\Framework\Patch;

use PHPUnit\Framework\Constraint\DirectoryExists;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\InvalidArgumentException;

trait LaravelAssertBackport
{
    /**
     * Asserts that a file does not exist.
     *
     * @param  string  $filename
     * @param  string  $message
     * @return void
     */
    public static function assertFileDoesNotExist(/*string */$filename, /*string */$message = '')/*: void*/
    {
        $message = backport_type_check('string', $message);

        $filename = backport_type_check('string', $filename);

        static::assertThat($filename, new LogicalNot(new FileExists), $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @param  string  $directory
     * @param  string  $message
     * @return void
     */
    public static function assertDirectoryDoesNotExist(/*string */$directory, /*string */$message = '')/*: void*/
    {
        $message = backport_type_check('string', $message);

        $directory = backport_type_check('string', $directory);

        static::assertThat($directory, new LogicalNot(new DirectoryExists), $message);
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param  string  $pattern
     * @param  string  $string
     * @param  string  $message
     * @return void
     */
    public static function assertMatchesRegularExpression(/*string */$pattern, /*string */$string, /*string */$message = '')/*: void*/
    {
        $message = backport_type_check('string', $message);

        $string = backport_type_check('string', $string);

        $pattern = backport_type_check('string', $pattern);

        static::assertThat($string, new RegularExpression($pattern), $message);
    }
}
