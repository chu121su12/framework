<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ErrorHandler\ErrorRenderer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Formats an exception using Serializer for rendering.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class SerializerErrorRenderer implements ErrorRendererInterface
{
    private $serializer;
    private $format;
    private $fallbackErrorRenderer;
    private $debug;

    /**
     * @param string|callable(FlattenException) $format The format as a string or a callable that should return it
     *                                                  formats not supported by Request::getMimeTypes() should be given as mime types
     * @param bool|callable                     $debug  The debugging mode as a boolean or a callable that should return it
     */
    public function __construct(SerializerInterface $serializer, $format, ErrorRendererInterface $fallbackErrorRenderer = null, $debug = false)
    {
        if (!\is_string($format) && !\is_callable($format)) {
            throw new \TypeError(sprintf('Argument 2 passed to "%s()" must be a string or a callable, "%s" given.', __METHOD__, \is_object($format) ? \get_class($format) : \gettype($format)));
        }

        if (!\is_bool($debug) && !\is_callable($debug)) {
            throw new \TypeError(sprintf('Argument 4 passed to "%s()" must be a boolean or a callable, "%s" given.', __METHOD__, \is_object($debug) ? \get_class($debug) : \gettype($debug)));
        }

        $this->serializer = $serializer;
        $this->format = $format;
        $this->fallbackErrorRenderer = isset($fallbackErrorRenderer) ? $fallbackErrorRenderer : new HtmlErrorRenderer();
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function render($exception)
    {
        $flattenException = FlattenException::createFromThrowable($exception);

        try {
            $thisFormat = $this->format;
            $format = \is_string($thisFormat) ? $thisFormat : $thisFormat($flattenException);
            $requestFormat = Request::getMimeTypes($format);
            $headers = [
                'Content-Type' => isset($requestFormat[0]) ? $requestFormat[0] : $format,
                'Vary' => 'Accept',
            ];

            $thisDebug = $this->debug;
            return $flattenException->setAsString($this->serializer->serialize($flattenException, $format, [
                'exception' => $exception,
                'debug' => \is_bool($thisDebug) ? $thisDebug : $thisDebug($exception),
            ]))
            ->setHeaders($flattenException->getHeaders() + $headers);
        } catch (NotEncodableValueException $e) {
            return $this->fallbackErrorRenderer->render($exception);
        }
    }

    public static function getPreferredFormat(RequestStack $requestStack)
    {
        return static function () use ($requestStack) {
            if (!$request = $requestStack->getCurrentRequest()) {
                throw new NotEncodableValueException();
            }

            return $request->getPreferredFormat();
        };
    }
}