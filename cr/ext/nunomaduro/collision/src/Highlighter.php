<?php

/*declare(strict_types=1);*/

namespace NunoMaduro\Collision;

/**
 * @internal
 */
final class Highlighter
{
    /*public */const TOKEN_DEFAULT = 'token_default';

    /*public */const TOKEN_COMMENT = 'token_comment';

    /*public */const TOKEN_STRING = 'token_string';

    /*public */const TOKEN_HTML = 'token_html';

    /*public */const TOKEN_KEYWORD = 'token_keyword';

    /*public */const ACTUAL_LINE_MARK = 'actual_line_mark';

    /*public */const LINE_NUMBER = 'line_number';

    /*private */const ARROW_SYMBOL = '>';

    /*private */const DELIMITER = '|';

    /*private */const ARROW_SYMBOL_UTF8 = '➜';

    /*private */const DELIMITER_UTF8 = '▕'; // '▶';

    /*private */const LINE_NUMBER_DIVIDER = 'line_divider';

    /*private */const MARKED_LINE_NUMBER = 'marked_line';

    /*private */const WIDTH = 3;

    /**
     * Holds the theme.
     *
     * @var array
     */
    /*private */const THEME = [
        self::TOKEN_STRING => ['light_gray'],
        self::TOKEN_COMMENT => ['dark_gray', 'italic'],
        self::TOKEN_KEYWORD => ['magenta', 'bold'],
        self::TOKEN_DEFAULT => ['default', 'bold'],
        self::TOKEN_HTML => ['blue', 'bold'],

        self::ACTUAL_LINE_MARK => ['red', 'bold'],
        self::LINE_NUMBER => ['dark_gray'],
        self::MARKED_LINE_NUMBER => ['italic', 'bold'],
        self::LINE_NUMBER_DIVIDER => ['dark_gray'],
    ];

    /** @var ConsoleColor */
    private /*ConsoleColor */$color;

    /*private */const DEFAULT_THEME = [
        self::TOKEN_STRING => 'red',
        self::TOKEN_COMMENT => 'yellow',
        self::TOKEN_KEYWORD => 'green',
        self::TOKEN_DEFAULT => 'default',
        self::TOKEN_HTML => 'cyan',

        self::ACTUAL_LINE_MARK => 'dark_gray',
        self::LINE_NUMBER => 'dark_gray',
        self::MARKED_LINE_NUMBER => 'dark_gray',
        self::LINE_NUMBER_DIVIDER => 'dark_gray',
    ];

    /** @var string */
    private /*string */$delimiter = self::DELIMITER_UTF8;

    /** @var string */
    private /*string */$arrow = self::ARROW_SYMBOL_UTF8;

    /*private */const NO_MARK = '    ';

    /**
     * Creates an instance of the Highlighter.
     */
    public function __construct(ConsoleColor $color = null, /*bool */$UTF8 = true)
    {
        $UTF8 = backport_type_check('bool', $UTF8);

        $this->color = $color ?: new ConsoleColor();

        foreach (self::DEFAULT_THEME as $name => $styles) {
            if (! $this->color->hasTheme($name)) {
                $this->color->addTheme($name, $styles);
            }
        }

        foreach (self::THEME as $name => $styles) {
            $this->color->addTheme($name, $styles);
        }
        if (! $UTF8) {
            $this->delimiter = self::DELIMITER;
            $this->arrow = self::ARROW_SYMBOL;
        }
        $this->delimiter .= ' ';
    }

    /**
     * Highlights the provided content.
     */
    public function highlight(/*string */$content, /*int */$line)/*: string*/
    {
        $content = backport_type_check('string', $content);
        $line = backport_type_check('int', $line);

        return rtrim($this->getCodeSnippet($content, $line, 4, 4));
    }

    /**
     * Highlights the provided content.
     *
     * @param  string  $source
     * @param  int  $lineNumber
     * @param  int  $linesBefore
     * @param  int  $linesAfter
     */
    public function getCodeSnippet(/*string */$source, /*int */$lineNumber, /*int */$linesBefore = 2, /*int */$linesAfter = 2)/*: string*/
    {
        $source = backport_type_check('string', $source);
        $lineNumber = backport_type_check('int', $lineNumber);
        $linesBefore = backport_type_check('int', $linesBefore);
        $linesAfter = backport_type_check('int', $linesAfter);

        $tokenLines = $this->getHighlightedLines($source);

        $offset = $lineNumber - $linesBefore - 1;
        $offset = max($offset, 0);
        $length = $linesAfter + $linesBefore + 1;
        $tokenLines = array_slice($tokenLines, $offset, $length, $preserveKeys = true);

        $lines = $this->colorLines($tokenLines);

        return $this->lineNumbers($lines, $lineNumber);
    }

    private function getHighlightedLines(/*string */$source)/*: array*/
    {
        $source = backport_type_check('string', $source);

        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $tokens = $this->tokenize($source);

        return $this->splitToLines($tokens);
    }

    private function tokenize(/*string */$source)/*: array*/
    {
        $source = backport_type_check('string', $source);

        $tokens = token_get_all($source);

        $output = [];
        $currentType = null;
        $buffer = '';
        $newType = null;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_WHITESPACE:
                        break;

                    case T_OPEN_TAG:
                    case T_OPEN_TAG_WITH_ECHO:
                    case T_CLOSE_TAG:
                    case T_STRING:
                    case T_VARIABLE:
                        // Constants
                    case T_DIR:
                    case T_FILE:
                    case T_METHOD_C:
                    case T_DNUMBER:
                    case T_LNUMBER:
                    case T_NS_C:
                    case T_LINE:
                    case T_CLASS_C:
                    case T_FUNC_C:
                    case T_TRAIT_C:
                        $newType = self::TOKEN_DEFAULT;
                        break;

                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        $newType = self::TOKEN_COMMENT;
                        break;

                    case T_ENCAPSED_AND_WHITESPACE:
                    case T_CONSTANT_ENCAPSED_STRING:
                        $newType = self::TOKEN_STRING;
                        break;

                    case T_INLINE_HTML:
                        $newType = self::TOKEN_HTML;
                        break;

                    default:
                        $newType = self::TOKEN_KEYWORD;
                }
            } else {
                $newType = $token === '"' ? self::TOKEN_STRING : self::TOKEN_KEYWORD;
            }

            if ($currentType === null) {
                $currentType = $newType;
            }

            if ($currentType !== $newType) {
                $output[] = [$currentType, $buffer];
                $buffer = '';
                $currentType = $newType;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
        }

        if (isset($newType)) {
            $output[] = [$newType, $buffer];
        }

        return $output;
    }

    private function splitToLines(array $tokens)/*: array*/
    {
        $lines = [];

        $line = [];
        foreach ($tokens as $token) {
            foreach (explode("\n", $token[1]) as $count => $tokenLine) {
                if ($count > 0) {
                    $lines[] = $line;
                    $line = [];
                }

                if ($tokenLine === '') {
                    continue;
                }

                $line[] = [$token[0], $tokenLine];
            }
        }

        $lines[] = $line;

        return $lines;
    }

    private function colorLines(array $tokenLines)/*: array*/
    {
        $lines = [];
        foreach ($tokenLines as $lineCount => $tokenLine) {
            $line = '';
            foreach ($tokenLine as $token) {
                list($tokenType, $tokenValue) = $token;
                if ($this->color->hasTheme($tokenType)) {
                    $line .= $this->color->apply($tokenType, $tokenValue);
                } else {
                    $line .= $tokenValue;
                }
            }
            $lines[$lineCount] = $line;
        }

        return $lines;
    }

    /**
     * @param  int|null  $markLine
     */
    private function lineNumbers(array $lines, /*int */$markLine = null)/*: string*/
    {
        $markLine = backport_type_check('?int', $markLine);

        $lineStrlen = strlen((string) ((int) array_key_last($lines) + 1));
        $lineStrlen = $lineStrlen < self::WIDTH ? self::WIDTH : $lineStrlen;
        $snippet = '';
        $mark = '  '.$this->arrow.' ';
        foreach ($lines as $i => $line) {
            $coloredLineNumber = $this->coloredLineNumber(self::LINE_NUMBER, $i, $lineStrlen);

            if ($markLine !== null) {
                $snippet .=
                    ($markLine === $i + 1
                        ? $this->color->apply(self::ACTUAL_LINE_MARK, $mark)
                        : self::NO_MARK
                    );

                $coloredLineNumber =
                    ($markLine === $i + 1 ?
                        $this->coloredLineNumber(self::MARKED_LINE_NUMBER, $i, $lineStrlen) :
                        $coloredLineNumber
                    );
            }
            $snippet .= $coloredLineNumber;

            $snippet .=
                $this->color->apply(self::LINE_NUMBER_DIVIDER, $this->delimiter);

            $snippet .= $line.PHP_EOL;
        }

        return $snippet;
    }

    /**
     * @param  string  $style
     * @param  int  $i
     * @param  int  $lineStrlen
     */
    private function coloredLineNumber(/*string */$style, /*int */$i, /*int */$length)/*: string*/
    {
        $style = backport_type_check('string', $style);
        $i = backport_type_check('int', $i);
        $length = backport_type_check('int', $length);

        return $this->color->apply($style, str_pad((string) ($i + 1), $length, ' ', STR_PAD_LEFT));
    }
}
