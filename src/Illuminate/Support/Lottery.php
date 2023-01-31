<?php

namespace Illuminate\Support;

use RuntimeException;

class Lottery
{
    /**
     * The number of expected wins.
     *
     * @var int|float
     */
    protected $chances;

    /**
     * The number of potential opportunities to win.
     *
     * @var int|null
     */
    protected $outOf;

    /**
     * The winning callback.
     *
     * @var null|callable
     */
    protected $winner;

    /**
     * The losing callback.
     *
     * @var null|callable
     */
    protected $loser;

    /**
     * The factory that should be used to generate results.
     *
     * @var callable|null
     */
    protected static $resultFactory;

    /**
     * Create a new Lottery instance.
     *
     * @param  int|float  $chances
     * @param  int|null  $outOf
     * @return void
     */
    public function __construct($chances, $outOf = null)
    {
        if ($outOf === null && is_float($chances) && $chances > 1) {
            throw new RuntimeException('Float must not be greater than 1.');
        }

        $this->chances = $chances;

        $this->outOf = $outOf;
    }

    /**
     * Create a new Lottery instance.
     *
     * @param  int|float  $chances
     * @param  int|null  $outOf
     * @return static
     */
    public static function odds($chances, $outOf = null)
    {
        return new static($chances, $outOf);
    }

    /**
     * Set the winner callback.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function winner($callback)
    {
        $this->winner = $callback;

        return $this;
    }

    /**
     * Set the loser callback.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function loser($callback)
    {
        $this->loser = $callback;

        return $this;
    }

    /**
     * Run the lottery.
     *
     * @param  mixed  ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return $this->runCallback(...$args);
    }

    /**
     * Run the lottery.
     *
     * @param  null|int  $times
     * @return mixed
     */
    public function choose($times = null)
    {
        if ($times === null) {
            return $this->runCallback();
        }

        $results = [];

        for ($i = 0; $i < $times; $i++) {
            $results[] = $this->runCallback();
        }

        return $results;
    }

    /**
     * Run the winner or loser callback, randomly.
     *
     * @param  mixed  ...$args
     * @return callable
     */
    protected function runCallback(...$args)
    {
        return $this->wins()
            ? call_user_func(isset($this->winner) ? $this->winner : function () { return true; }, ...$args)
            : call_user_func(isset($this->loser) ? $this->loser : function () { return false; }, ...$args);
    }

    /**
     * Determine if the lottery "wins" or "loses".
     *
     * @return bool
     */
    protected function wins()
    {
        $fn = static::resultFactory();

        return $fn($this->chances, $this->outOf);
    }

    /**
     * The factory that determines the lottery result.
     *
     * @return callable
     */
    protected static function resultFactory()
    {
        return isset(static::$resultFactory) ? static::$resultFactory : function ($chances, $outOf) { return $outOf === null
            ? random_int(0, PHP_INT_MAX) / PHP_INT_MAX <= $chances
            : random_int(1, $outOf) <= $chances;
        };
    }

    /**
     * Force the lottery to always result in a win.
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function alwaysWin($callback = null)
    {
        self::setResultFactory(function () { return true; });

        if ($callback === null) {
            return;
        }

        $callback();

        static::determineResultNormally();
    }

    /**
     * Force the lottery to always result in a lose.
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function alwaysLose($callback = null)
    {
        self::setResultFactory(function () { return false; });

        if ($callback === null) {
            return;
        }

        $callback();

        static::determineResultNormally();
    }

    /**
     * Set the sequence that will be used to determine lottery results.
     *
     * @param  array  $sequence
     * @param  callable|null  $whenMissing
     * @return void
     */
    public static function fix($sequence, $whenMissing = null)
    {
        return static::forceResultWithSequence($sequence, $whenMissing);
    }

    /**
     * Set the sequence that will be used to determine lottery results.
     *
     * @param  array  $sequence
     * @param  callable|null  $whenMissing
     * @return void
     */
    public static function forceResultWithSequence($sequence, $whenMissing = null)
    {
        $next = 0;

        $whenMissing  = isset($whenMissing) ? $whenMissing : function ($chances, $outOf) use (&$next) {
            $factoryCache = static::$resultFactory;

            static::$resultFactory = null;

            $fn = static::resultFactory();

            $result = $fn($chances, $outOf);

            static::$resultFactory = $factoryCache;

            $next++;

            return $result;
        };

        static::setResultFactory(function ($chances, $outOf) use (&$next, $sequence, $whenMissing) {
            if (array_key_exists($next, $sequence)) {
                return $sequence[$next++];
            }

            return $whenMissing($chances, $outOf);
        });
    }

    /**
     * Indicate that the lottery results should be determined normally.
     *
     * @return void
     */
    public static function determineResultNormally()
    {
        static::$resultFactory = null;
    }

    /**
     * Set the factory that should be used to determine the lottery results.
     *
     * @param  callable  $factory
     * @return void
     */
    public static function setResultFactory($factory)
    {
        self::$resultFactory = $factory;
    }
}
