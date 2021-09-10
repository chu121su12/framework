<?php

namespace Facade\Ignition\LogRecorder;

use Illuminate\Log\Events\MessageLogged;

class LogMessage
{
    /** @var string */
    protected $message;

    /** @var array */
    protected $context;

    /** @var string */
    protected $level;

    /** @var float */
    protected $microtime;

    public function __construct(/*?string */$message = null, /*string */$level, array $context = [], /*?float */$microtime = null)
    {
        $message = cast_to_string($message, null);
        $level = cast_to_string($level);
        $microtime = cast_to_float($microtime, null);

        $this->message = $message;
        $this->level = $level;
        $this->context = $context;
        $this->microtime = isset($microtime) ? $microtime : microtime(true);
    }

    public static function fromMessageLoggedEvent(MessageLogged $event)/*: self*/
    {
        return new self(
            $event->message,
            $event->level,
            $event->context
        );
    }

    public function toArray()
    {
        return [
            'message' => $this->message,
            'level' => $this->level,
            'context' => $this->context,
            'microtime' => $this->microtime,
        ];
    }
}
