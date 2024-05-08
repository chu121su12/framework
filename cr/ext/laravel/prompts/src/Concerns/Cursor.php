<?php

namespace Laravel\Prompts\Concerns;

trait Cursor
{
    /**
     * Indicates if the cursor has been hidden.
     */
    protected static /*bool */$cursorHidden = false;

    /**
     * Hide the cursor.
     */
    public function hideCursor()/*: void*/
    {
        static::writeDirectly("\e[?25l");

        static::$cursorHidden = true;
    }

    /**
     * Show the cursor.
     */
    public function showCursor()/*: void*/
    {
        static::writeDirectly("\e[?25h");

        static::$cursorHidden = false;
    }

    /**
     * Restore the cursor if it was hidden.
     */
    public function restoreCursor()/*: void*/
    {
        if (static::$cursorHidden) {
            $this->showCursor();
        }
    }

    /**
     * Move the cursor.
     */
    public function moveCursor(/*int */$x, /*int */$y = 0)/*: void*/
    {
        $y = backport_type_check('int', $y);

        $x = backport_type_check('int', $x);

        $sequence = '';

        if ($x < 0) {
            $sequence .= "\e[".abs($x).'D'; // Left
        } elseif ($x > 0) {
            $sequence .= "\e[{$x}C"; // Right
        }

        if ($y < 0) {
            $sequence .= "\e[".abs($y).'A'; // Up
        } elseif ($y > 0) {
            $sequence .= "\e[{$y}B"; // Down
        }

        static::writeDirectly($sequence);
    }

    /**
     * Move the cursor to the given column.
     */
    public function moveCursorToColumn(/*int */$column)/*: void*/
    {
        $column = backport_type_check('int', $column);

        static::writeDirectly("\e[{$column}G");
    }

    /**
     * Move the cursor up by the given number of lines.
     */
    public function moveCursorUp(/*int */$lines)/*: void*/
    {
        $lines = backport_type_check('int', $lines);

        static::writeDirectly("\e[{$lines}A");
    }
}
