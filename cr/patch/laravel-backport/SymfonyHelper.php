<?php

namespace CR\LaravelBackport;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SymfonyHelper
{
    public static function headerUtilsQuote($s)
    {
        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"'.addcslashes($s, '"\\"').'"';
    }

    public static function headerUtilsToString($assoc, $separator)
    {
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

    public static function httpFoundationMakeDisposition($disposition, $filename, $filenameFallback = '')
    {
        if (!\in_array($disposition, [ResponseHeaderBag::DISPOSITION_ATTACHMENT, ResponseHeaderBag::DISPOSITION_INLINE])) {
            throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', ResponseHeaderBag::DISPOSITION_ATTACHMENT, ResponseHeaderBag::DISPOSITION_INLINE));
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
}
