<?php

namespace CR\LaravelBackport;

use ReflectionClass;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag5;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\VarDumper\Caster\Caster;

class SymfonyHelper
{
    const CONSOLE_SUCCESS = 0;

    const CONSOLE_FAILURE = 1;

    const CONSOLE_ANSI_REPLACEMENTS = [
        '/ › /' => ' > ',
        '/…/' => '~',
        '/⇂ /' => '| ',
        '/❯ /' => '> ',
    ];

    const CONSOLE_STYLE_REPLACEMENTS = [
        '/<fg=#6C7280>/' => '<fg=cyan>',
        '/<fg=#ef8414;/' => '<fg=yellow;',
        '/<fg=bright-blue>/' => '<fg=blue>',
        '/<fg=gray>/' => '<fg=black>',
    ];

    const DISPOSITION_ATTACHMENT = 'attachment';

    const DISPOSITION_INLINE = 'inline';

    protected static $terminal;

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
        $s = backport_type_check('string', $s);

        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"'.addcslashes($s, '"\\"').'"';
    }

    public static function headerUtilsSplit(/*string */$header, /*string */$separators)////: array
    {
        $separators = backport_type_check('string', $separators);

        $header = backport_type_check('string', $header);

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
        $separator = backport_type_check('string', $separator);

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
        $disposition = backport_type_check('string', $disposition);

        $filename = backport_type_check('string', $filename);

        $filenameFallback = backport_type_check('string', $filenameFallback);

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
        $cwd = backport_type_check('?string', $cwd);

        $timeout = backport_type_check('?float', $timeout);

        if (windows_os()) {
            return new Process($command, $cwd, null, $input, $timeout);
        }

        return new Process($command, $cwd, $env, $input, $timeout);
    }

    public static function processFromShellCommandline($command, $cwd = null, array $env = null, $input = null, $timeout = 60)
    {
        $command = backport_type_check('string', $command);

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
        $status = backport_type_check('int', $status);
        $public = backport_type_check('bool', $public);
        $contentDisposition = backport_type_check('?string', $contentDisposition);
        $autoEtag = backport_type_check('bool', $autoEtag);
        $autoLastModified = backport_type_check('bool', $autoLastModified);

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
        $status = backport_type_check('int', $status);

        $response = new StreamedResponse($callback, $status);

        $response->headers = new ResponseHeaderBag5($headers);

        return $response;
    }

    public static function getTerminal($default = null)
    {
        if ($default) {
            return $default;
        }

        if (! static::$terminal) {
            static::$terminal = $default ?: new \Symfony\Component\Console\Terminal;
        }

        return static::$terminal;
    }

    private static function groupParts(array $matches, /*string */$separators, /*bool */$first = true)////: array
    {
        $separators = backport_type_check('string', $separators);

        $first = backport_type_check('bool', $first);

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

    public static function consoleOutputMessage($messages, $ansi)
    {
        $messages = \preg_replace(\array_keys(self::CONSOLE_STYLE_REPLACEMENTS), \array_values(self::CONSOLE_STYLE_REPLACEMENTS), $messages);

        return $ansi
            ? \preg_replace(\array_keys(self::CONSOLE_ANSI_REPLACEMENTS), \array_values(self::CONSOLE_ANSI_REPLACEMENTS), $messages)
            : $messages;
    }

    public static function consoleOutputStyle($messages, OutputInterface $output, $ansi = false)
    {
        if (\is_string($messages)) {
            if (! $ansi) {
                try {
                    $consoleInput = optional((new ReflectionClass($output))->getParentClass())->getProperty('input');
                    if ($consoleInput) {
                        $inputValue = tap($consoleInput)->setAccessible(true)->getValue($output);
                        $ansi = (bool) $inputValue->getOption('ansi');
                    }
                } catch (\Exception $e) {
                } catch (\Throwable $e) {
                }
            }

            return self::consoleOutputMessage($messages, $ansi);
        }

        if (! \is_array($messages)) {
            return $messages;
        }

        foreach ($messages as $key => $value) {
            $messages[$key] = self::consoleOutputStyle($value, $output, $ansi);
        }

        return $messages;
    }

    public static function varDumperUnsetClosureFileInfoReflectionCaster()
    {
        return ['Closure' => function (\Closure $c, array $a) {
            unset($a[Caster::PREFIX_VIRTUAL.'file'], $a[Caster::PREFIX_VIRTUAL.'line']);

            return $a;
        }];
    }

    public static function normalizeRequestPreCreateFromGlobals()
    {
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            foreach ($_FILES as $key => $value) {
                unset($value['full_path']);
                $_FILES[$key] = $value;
            }
        }
    }
}
