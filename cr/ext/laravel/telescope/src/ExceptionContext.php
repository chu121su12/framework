<?php

namespace Laravel\Telescope;

use Illuminate\Support\Str;
use Throwable;

class ExceptionContext
{
    /**
     * Get the exception code context for the given exception.
     *
     * @param  \Throwable  $exception
     * @return array
     */
    public static function get($exception)
    {
        $evalContext = static::getEvalContext($exception);

        return isset($evalContext) ? $evalContext :
            static::getFileContext($exception);
    }

    /**
     * Get the exception code context when eval() failed.
     *
     * @param  \Throwable  $exception
     * @return array|null
     */
    protected static function getEvalContext($exception)
    {
        if (Str::contains($exception->getFile(), "eval()'d code")) {
            return [
                $exception->getLine() => "eval()'d code",
            ];
        }
    }

    /**
     * Get the exception code context from a file.
     *
     * @param  \Throwable  $exception
     * @return array
     */
    protected static function getFileContext($exception)
    {
        return collect(explode("\n", file_get_contents($exception->getFile())))
            ->slice($exception->getLine() - 10, 20)
            ->mapWithKeys(function ($value, $key) {
                return [$key + 1 => $value];
            })->all();
    }
}
