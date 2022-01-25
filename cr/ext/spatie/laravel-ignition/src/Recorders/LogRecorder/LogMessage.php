<?php

namespace Spatie\LaravelIgnition\Recorders\LogRecorder;

use Illuminate\Log\Events\MessageLogged;

class LogMessage
{
    protected ?string $message;

    protected string $level;

    /** @var array<string, string> */
    protected array $context = [];

    protected ?float $microtime;

    /**
     * @param string|null $message
     * @param string $level
     * @param array<string, string> $context
     * @param float|null $microtime
     */
    public function __construct(
        /*?string */$message = null,
        string $level,
        array $context = [],
        ?float $microtime = null
    ) {
        $message = cast_to_string($message, null);

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

    /** @return array<string, mixed> */
    public function toArray()/*: array*/
    {
        return [
            'message' => $this->message,
            'level' => $this->level,
            'context' => $this->context,
            'microtime' => $this->microtime,
        ];
    }
}
