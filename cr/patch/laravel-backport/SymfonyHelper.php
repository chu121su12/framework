<?php

namespace CR\LaravelBackport;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Process\Process;

class SymfonyHelper
{
    const DISPOSITION_ATTACHMENT = 'attachment';
    const DISPOSITION_INLINE = 'inline';

    public static function headerUtilsQuote(/*string */$s)////: string
    {
        $s = cast_to_string($s);

        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"'.addcslashes($s, '"\\"').'"';
    }

    public static function headerUtilsToString(array $assoc, /*string */$separator)////: string
    {
        $separator = cast_to_string($separator);

        $parts = [];
        foreach ($assoc as $name => $value) {
            if (true === $value) {
                $parts[] = $name;
            } else {
                $parts[] = $name.'='.self::headerUtilsQuote($value);
            }
        }

        return implode($separator.' ', $parts);
    }

    public static function httpFoundationMakeDisposition(/*string */$disposition, /*string */$filename, /*string */$filenameFallback = '')////: string
    {
        $disposition = cast_to_string($disposition);

        $filename = cast_to_string($filename);

        $filenameFallback = cast_to_string($filenameFallback);

        if (!\in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE])) {
            throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE));
        }

        if ('' === $filenameFallback) {
            $filenameFallback = $filename;
        }

        // filenameFallback is not ASCII.
        if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback)) {
            throw new \InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        }

        // percent characters aren't safe in fallback.
        if (false !== strpos($filenameFallback, '%')) {
            throw new \InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        // path separators aren't allowed in either.
        if (false !== strpos($filename, '/') || false !== strpos($filename, '\\') || false !== strpos($filenameFallback, '/') || false !== strpos($filenameFallback, '\\')) {
            throw new \InvalidArgumentException('The filename and the fallback cannot contain the "/" and "\\" characters.');
        }

        $params = ['filename' => $filenameFallback];
        if ($filename !== $filenameFallback) {
            $params['filename*'] = "utf-8''".rawurlencode($filename);
        }

        return $disposition.'; '.self::headerUtilsToString($params, ';');
    }

    public static function consoleStrlen($string = null)
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return \strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    public static function newProcess($command, $cwd = null, array $env = null, $input = null, $timeout = 60)
    {
        $cwd = cast_to_string($cwd, null);

        $timeout = cast_to_float($timeout, null);

        if (windows_os()) {
            return new Process($command, $cwd, null, $input, $timeout);
        }

        return new Process($command, $cwd, $env, $input, $timeout);
    }

    public static function processFromShellCommandline($command, $cwd = null, array $env = null, $input = null, $timeout = 60)
    {
        $command = cast_to_string($command);

        return static::newProcess($command, $cwd, $env, $input, $timeout);
    }

    public static function consoleApplicationRenderThrowable($e, $output)
    {
        $console = new ConsoleApplication;

        if (method_exists($console, 'renderThrowable')) {

            $console->renderThrowable($e, $output);

        } else {
            if (!($e instanceof \Exception)) {
                $e = new \Exception($e);
            }

            $console->renderException($e, $output);
        }
    }

}
