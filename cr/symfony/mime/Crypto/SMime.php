<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Crypto;

use Symfony\Component\Mime\Exception\RuntimeException;
use Symfony\Component\Mime\Part\SMimePart;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @internal
 */
abstract class SMime
{
    protected function normalizeFilePath($path) //// string
    {
        $path = cast_to_string($path);

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('File does not exist: "%s".', $path));
        }

        return 'file://'.str_replace('\\', '/', realpath($path));
    }

    protected function iteratorToFile($iterator, $stream) /// void
    {
        $iterator = cast_to_iterable($iterator);

        foreach ($iterator as $chunk) {
            fwrite($stream, $chunk);
        }
    }

    protected function convertMessageToSMimePart($stream, $type, $subtype) // SMimePart
    {
        $subtype = cast_to_string($subtype);

        $type = cast_to_string($type);

        rewind($stream);

        $headers = '';

        while (!feof($stream)) {
            $buffer = fread($stream, 78);
            $headers .= $buffer;

            // Detect ending of header list
            if (preg_match('/(\r\n\r\n|\n\n)/', $headers, $match)) {
                $headersPosEnd = strpos($headers, $headerBodySeparator = $match[0]);

                break;
            }
        }

        $headers = $this->getMessageHeaders(trim(substr($headers, 0, $headersPosEnd)));

        fseek($stream, $headersPosEnd + \strlen($headerBodySeparator));

        return new SMimePart($this->getStreamIterator($stream), $type, $subtype, $this->getParametersFromHeader($headers['content-type']));
    }

    protected function getStreamIterator($stream) //// iterable
    {
        while (!feof($stream)) {
            yield str_replace("\n", "\r\n", str_replace("\r\n", "\n", fread($stream, 16372)));
        }
    }

    private function getMessageHeaders($headerData) //// array
    {
        $headerData = cast_to_string($headerData);

        $headers = [];
        $headerLines = explode("\r\n", str_replace("\n", "\r\n", str_replace("\r\n", "\n", $headerData)));
        $currentHeaderName = '';

        // Transform header lines into an associative array
        foreach ($headerLines as $headerLine) {
            // Empty lines between headers indicate a new mime-entity
            if ('' === $headerLine) {
                break;
            }

            // Handle headers that span multiple lines
            if (false === strpos($headerLine, ':')) {
                $headers[$currentHeaderName] .= ' '.trim($headerLine);
                continue;
            }

            $header = explode(':', $headerLine, 2);
            $currentHeaderName = strtolower($header[0]);
            $headers[$currentHeaderName] = trim($header[1]);
        }

        return $headers;
    }

    private function getParametersFromHeader($header) //// array
    {
        $header = cast_to_string($header);

        $params = [];

        preg_match_all('/(?P<name>[a-z-0-9]+)=(?P<value>"[^"]+"|(?:[^\s;]+|$))(?:\s+;)?/i', $header, $matches);

        foreach ($matches['value'] as $pos => $paramValue) {
            $params[$matches['name'][$pos]] = trim($paramValue, '"');
        }

        return $params;
    }
}