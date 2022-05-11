<?php

namespace CR\LaravelBackport;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag5;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Process\Process;

class SymfonyHelper
{
    const DISPOSITION_ATTACHMENT = 'attachment';
    const DISPOSITION_INLINE = 'inline';

    public static function headerUtilsCombine(array $parts)////: array
    {
        $assoc = [];
        foreach ($parts as $part) {
            $name = strtolower($part[0]);
            $value = isset($part[1]) ? $part[1] : true;
            $assoc[$name] = $value;
        }

        return $assoc;
    }

    public static function headerUtilsQuote(/*string */$s)////: string
    {
        $s = cast_to_string($s);

        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"'.addcslashes($s, '"\\"').'"';
    }

    public static function headerUtilsSplit(/*string */$header, /*string */$separators)////: array
    {
        $separators = cast_to_string($separators);

        $header = cast_to_string($header);

        $quotedSeparators = preg_quote($separators, '/');

        preg_match_all('
            /
                (?!\s)
                    (?:
                        # quoted-string
                        "(?:[^"\\\\]|\\\\.)*(?:"|\\\\|$)
                    |
                        # token
                        [^"'.$quotedSeparators.']+
                    )+
                (?<!\s)
            |
                # separator
                \s*
                (?<separator>['.$quotedSeparators.'])
                \s*
            /x', trim($header), $matches, \PREG_SET_ORDER);

        return self::groupParts($matches, $separators);
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

    public static function headerUtilsUnquote(/*string */$s)////: string
    {
        return preg_replace('/\\\\(.)|"/', '$1', $s);
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

    public static function prepareTooManyRequestsHttpException(TooManyRequestsHttpException $exception, $retryAfter = null, array $headers = [])
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }

        $exception->setHeaders($headers);

        return $exception;
    }

    public static function makeBinaryFileResponse($file, /*int */$status = 200, array $headers = [], /*bool */$public = true, /*string */$contentDisposition = null, /*bool */$autoEtag = false, /*bool */$autoLastModified = true)
    {
        $status = cast_to_int($status);
        $public = cast_to_bool($public);
        $contentDisposition = cast_to_string($contentDisposition, null);
        $autoEtag = cast_to_bool($autoEtag);
        $autoLastModified = cast_to_bool($autoLastModified);

        $response = new BinaryFileResponse($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);

        $response->headers = new ResponseHeaderBag5($headers);

        $response->setFile($file, $contentDisposition, $autoEtag, $autoLastModified);

        if ($public) {
            $response->setPublic();
        }

        return $response;
    }

    public static function makeStreamedResponse(callable $callback = null, /*int */$status = 200, array $headers = [])
    {
        $status = cast_to_int($status);

        $response = new StreamedResponse($callback, $status);

        $response->headers = new ResponseHeaderBag5($headers);

        return $response;
    }

    private static function groupParts(array $matches, /*string */$separators, /*bool */$first = true)////: array
    {
        $separators = cast_to_string($separators);

        $first = cast_to_bool($first);

        $separator = $separators[0];
        $partSeparators = substr($separators, 1);

        $i = 0;
        $partMatches = [];
        $previousMatchWasSeparator = false;
        foreach ($matches as $match) {
            if (!$first && $previousMatchWasSeparator && isset($match['separator']) && $match['separator'] === $separator) {
                $previousMatchWasSeparator = true;
                $partMatches[$i][] = $match;
            } elseif (isset($match['separator']) && $match['separator'] === $separator) {
                $previousMatchWasSeparator = true;
                ++$i;
            } else {
                $previousMatchWasSeparator = false;
                $partMatches[$i][] = $match;
            }
        }

        $parts = [];
        if ($partSeparators) {
            foreach ($partMatches as $matches) {
                $parts[] = self::groupParts($matches, $partSeparators, false);
            }
        } else {
            foreach ($partMatches as $matches) {
                $parts[] = self::headerUtilsUnquote($matches[0][0]);
            }

            if (!$first && 2 < \count($parts)) {
                $parts = [
                    $parts[0],
                    implode($separator, \array_slice($parts, 1)),
                ];
            }
        }

        return $parts;
    }
}
