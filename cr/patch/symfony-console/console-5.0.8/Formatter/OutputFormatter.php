<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Formatter;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Formatter class for console output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class OutputFormatter implements WrappableOutputFormatterInterface
{
    private $decorated;
    private $styles = [];
    private $styleStack;

    /**
     * Escapes "<" special char in given text.
     *
     * @return string Escaped text
     */
    public static function escape($text)
    {
        $text = cast_to_string($text);

        $text = preg_replace('/([^\\\\]?)</', '$1\\<', $text);

        return self::escapeTrailingBackslash($text);
    }

    /**
     * Escapes trailing "\" in given text.
     *
     * @internal
     */
    public static function escapeTrailingBackslash($text)
    {
        $text = cast_to_string($text);

        if ('\\' === substr($text, -1)) {
            $len = \strlen($text);
            $text = rtrim($text, '\\');
            $text = str_replace("\0", '', $text);
            $text .= str_repeat("\0", $len - \strlen($text));
        }

        return $text;
    }

    /**
     * Initializes console output formatter.
     *
     * @param OutputFormatterStyleInterface[] $styles Array of "name => FormatterStyle" instances
     */
    public function __construct($decorated = false, array $styles = [])
    {
        $decorated = cast_to_bool($decorated);

        $this->decorated = $decorated;

        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('info', new OutputFormatterStyle('green'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }

        $this->styleStack = new OutputFormatterStyleStack();
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        $decorated = cast_to_bool($decorated);

        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return $this->decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        $name = cast_to_string($name);

        $this->styles[strtolower($name)] = $style;
    }

    /**
     * {@inheritdoc}
     */
    public function hasStyle($name)
    {
        $name = cast_to_string($name);

        return isset($this->styles[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle($name)
    {
        $name = cast_to_string($name);

        if (!$this->hasStyle($name)) {
            throw new InvalidArgumentException(sprintf('Undefined style: "%s".', $name));
        }

        return $this->styles[strtolower($name)];
    }

    /**
     * {@inheritdoc}
     */
    public function format($message = null)
    {
        $message = cast_to_string($message, null);

        return $this->formatAndWrap($message, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function formatAndWrap($message = null, $width)
    {
        $width = cast_to_int($width);

        $message = cast_to_string($message, null);

        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][^<>]*+';
        $currentLineLength = 0;
        preg_match_all("#<(($tagRegex) | /($tagRegex)?)>#ix", $message, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];

            if (0 != $pos && '\\' == $message[$pos - 1]) {
                continue;
            }

            // add the text up to the next tag
            $output .= $this->applyCurrentStyle(substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $offset = $pos + \strlen($text);

            // opening tag?
            if ($open = '/' != $text[1]) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = isset($matches[3][$i][0]) ? $matches[3][$i][0] : '';
            }

            if (!$open && !$tag) {
                // </>
                $this->styleStack->pop();
            } elseif (null === $style = $this->createStyleFromString($tag)) {
                $output .= $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
            } elseif ($open) {
                $this->styleStack->push($style);
            } else {
                $this->styleStack->pop($style);
            }
        }

        $output .= $this->applyCurrentStyle(substr($message, $offset), $output, $width, $currentLineLength);

        if (false !== strpos($output, "\0")) {
            return strtr($output, ["\0" => '\\', '\\<' => '<']);
        }

        return str_replace('\\<', '<', $output);
    }

    /**
     * @return OutputFormatterStyleStack
     */
    public function getStyleStack()
    {
        return $this->styleStack;
    }

    /**
     * Tries to create new style instance from string.
     */
    private function createStyleFromString($string)
    {
        $string = cast_to_string($string);

        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if (!preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $style = new OutputFormatterStyle();
        foreach ($matches as $match) {
            array_shift($match);
            $match[0] = strtolower($match[0]);

            if ('fg' == $match[0]) {
                $style->setForeground(strtolower($match[1]));
            } elseif ('bg' == $match[0]) {
                $style->setBackground(strtolower($match[1]));
            } elseif ('href' === $match[0]) {
                $style->setHref($match[1]);
            } elseif ('options' === $match[0]) {
                preg_match_all('([^,;]+)', strtolower($match[1]), $options);
                $options = array_shift($options);
                foreach ($options as $option) {
                    $style->setOption($option);
                }
            } else {
                return null;
            }
        }

        return $style;
    }

    /**
     * Applies current style from stack to text, if must be applied.
     */
    private function applyCurrentStyle($text, $current, $width, &$currentLineLength)
    {
        $width = cast_to_int($width);

        $current = cast_to_string($current);

        $text = cast_to_string($text);

        $currentLineLength = cast_to_int($currentLineLength);

        if ('' === $text) {
            return '';
        }

        if (!$width) {
            return $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text;
        }

        if (!$currentLineLength && '' !== $current) {
            $text = ltrim($text);
        }

        if ($currentLineLength) {
            $prefix = substr($text, 0, $i = $width - $currentLineLength)."\n";
            $text = substr($text, $i);
        } else {
            $prefix = '';
        }

        preg_match('~(\\n)$~', $text, $matches);
        $text = $prefix.preg_replace('~([^\\n]{'.$width.'})\\ *~', "\$1\n", $text);
        $text = rtrim($text, "\n").(isset($matches[1]) ? $matches[1] : '');

        if (!$currentLineLength && '' !== $current && "\n" !== substr($current, -1)) {
            $text = "\n".$text;
        }

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $currentLineLength += \strlen($line);
            if ($width <= $currentLineLength) {
                $currentLineLength = 0;
            }
        }

        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }

        return implode("\n", $lines);
    }
}