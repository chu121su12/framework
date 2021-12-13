<?php

namespace Illuminate\Http\Exceptions;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ThrottleRequestsException extends TooManyRequestsHttpException
{
    /**
     * Create a new throttle requests exception instance.
     *
     * @param  string  $message
     * @param  \Throwable|null  $previous
     * @param  array  $headers
     * @param  int  $code
     * @return void
     */
    public function __construct($message = '', /*Throwable */$previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(null, $message, $previous, $code);

        SymfonyHelper::prepareTooManyRequestsHttpException($this, null, $headers);
    }
}
