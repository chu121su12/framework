<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\MessageFormatter;

use ReflectionMethod;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;

// @codeCoverageIgnoreStart
$transMethod = new ReflectionMethod(MessageFormatterInterface::class, 'format');

if (version_compare(PHP_VERSION, '8.0', '<')) {
    require __DIR__.'/../../../lazy/Carbon/MessageFormatter/MessageFormatterMapperWeakType.php';
}
else {
    require $transMethod->getParameters()[0]->hasType()
        ? __DIR__.'/../../../lazy/Carbon/MessageFormatter/MessageFormatterMapperStrongType.php'
        : __DIR__.'/../../../lazy/Carbon/MessageFormatter/MessageFormatterMapperWeakType.php';
}
// @codeCoverageIgnoreEnd

final class MessageFormatterMapper extends LazyMessageFormatter
{
    /**
     * Wrapped formatter.
     *
     * @var MessageFormatterInterface
     */
    protected $formatter;

    public function __construct(/*?*/MessageFormatterInterface $formatter = null)
    {
        $this->formatter = isset($formatter) ? $formatter : new MessageFormatter();
    }

    protected function transformLocale(/*?string */$locale = null)/*: ?string*/
    {
        $locale = backport_type_check('?string', $locale);

        return $locale ? preg_replace('/[_@][A-Za-z][a-z]{2,}/', '', $locale) : $locale;
    }
}
