<?php

/**
 * @copyright Copyright © 2022 Anton Smirnov
 * @license BSD-3-Clause https://spdx.org/licenses/BSD-3-Clause.html
 *
 * Includes adaptation of C code from the PHP Interpreter
 * @license PHP-3.01 https://spdx.org/licenses/PHP-3.01.html
 * @see https://github.com/php/php-src/blob/eff9aed/ext/random/randomizer.c
 * @see https://github.com/php/php-src/blob/eff9aed/ext/standard/array.c
 * @see https://github.com/php/php-src/blob/eff9aed/ext/standard/string.c
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

/*declare(strict_types=1);*/

namespace Symfony\Polyfill\Php82\Random;

use Brick\Math\BigInteger;
use Closure;
use Error;
use Exception;
use Random\BrokenRandomEngineError;
use Random\CryptoSafeEngine;
use Random\Engine;
use Random\Engine\Mt19937;
use Random\Engine\Secure;
use Random\RandomException;
use Serializable;
use Symfony\Polyfill\Php82\NoDynamicProperties;
use ValueError;

/**
 * @property-read Engine $engine
 */
class Randomizer implements Serializable
{
    use NoDynamicProperties {
        __set as nodyn__set;
    }

    /*public */const SIZEOF_UINT32_T = 4;
    /*public */const SIZEOF_UINT64_T = 8;

    /*private */const PHP_MT_RAND_MAX = 0x7FFFFFFF;
    /*private */const RANDOM_RANGE_ATTEMPTS = 50;

    /** @var BigInteger */
    private static $UINT32_ZERO;
    /** @var BigInteger */
    private static $UINT32_MAX;
    /** @var BigInteger */
    private static $UINT64_ZERO;
    /** @var BigInteger */
    private static $UINT64_MAX;
    /** @var BigInteger */
    private static $UINT32_MAX_64;

    /** @var Engine */
    private $engine;

    public function __construct(/*?*/Engine $engine = null)
    {
        $this->initMath();

        /** @psalm-suppress RedundantConditionGivenDocblockType not yet initialized */
        if ($this->engine !== null) {
            throw new Error('Cannot modify readonly property Random\Randomizer::$engine');
        }

        $this->engine = isset($engine) ? $engine : new Secure;
    }

    /**
     * @codeCoverageIgnore
     * @psalm-suppress DocblockTypeContradiction the "constants" are initialized here
     */
    private function initMath()/*: void*/
    {
        self::$UINT32_ZERO = BigInteger::zero();
        self::$UINT32_MAX  = BigInteger::fromBase('ffffffff', 16);

        self::$UINT64_ZERO = BigInteger::zero();
        self::$UINT64_MAX  = BigInteger::fromBase('ffffffffffffffff', 16);

        self::$UINT32_MAX_64 = self::$UINT32_MAX;
    }

    private function generate()/*: string*/
    {
        $retval = $this->engine->generate();

        $size = \strlen($retval);

        if ($size === 0) {
            throw new BrokenRandomEngineError('A random engine must return a non-empty string');
        } elseif ($size > self::SIZEOF_UINT64_T) {
            $retval = \substr($retval, 0, self::SIZEOF_UINT64_T);
        }

        return $retval;
    }

    public function getInt(/*int */$min, /*int */$max)/*: int*/
    {
        $min = backport_type_check('int', $min);

        $max = backport_type_check('int', $max);

        if ($max < $min) {
            throw new ValueError(
                __METHOD__ . '(): Argument #2 ($max) must be greater than or equal to argument #1 ($min)'
            );
        }

        // handle MT_RAND_PHP
        /** @psalm-suppress PossiblyInvalidFunctionCall */
        if (
            class_exists(Mt19937::class) && $this->engine instanceof Mt19937 &&
            call_user_func(Closure::bind(function () {
                /** @psalm-suppress UndefinedThisPropertyFetch */
                return $this->mode === \MT_RAND_PHP; // read private property
            }, $this->engine, $this->engine))
        ) {
            return $this->rangeBadscaling($min, $max);
        }

        return $this->doGetInt($min, $max);
    }

    private function doGetInt(/*int */$min, /*int */$max)/*: int*/
    {
        $min = backport_type_check('int', $min);

        $max = backport_type_check('int', $max);

        // special handler for Secure
        if (
            $this->engine instanceof Secure
        ) {
            try {
                return \random_int($min, $max);
                // @codeCoverageIgnoreStart
                // catch unreproducible
            } catch (\Exception $e) {
                // random_bytes throws Exception in <= 8.1 but RandomException in >= 8.2
                throw new RandomException($e->getMessage(), (int)$e->getCode(), $e->getPrevious());
                // @codeCoverageIgnoreEnd
            }
        }

        $umax = BigInteger::of($max)->minus($min);

        if ($umax->compareTo(self::$UINT32_MAX_64) > 0) {
            $rangeval = $this->range64($umax);
            return $this->toSignedInt(8, BigInteger::of($rangeval)->plus($min));
        } else {
            $umax = BigInteger::of($max) // self::$math32
                ->minus($min); // self::$math32
            $rangeval = $this->range32($umax);
            $int = $rangeval
                ->plus($min) // self::$math32
                ->toInt();
            return $this->toSignedInt(4, $int);
        }
    }

    /**
     * @param BigInteger $umax
     * @return BigInteger
     */
    private function range32($umax)
    {
        $result = '';
        do {
            $result .= $this->generate();
        } while (\strlen($result) < self::SIZEOF_UINT32_T);

        $result = $this->fromBinary(4, $result);

        if ($umax->isEqualTo(self::$UINT32_MAX)) {
            return $result;
        }

        $umax1 = $umax;
        $umax = BigInteger::of($umax)->plus(1);

        if ($umax->and_($umax1)->isZero()) {
            return $result->and_($umax1);
        }

        $limit = //self::$UINT32_MAX - (self::$UINT32_MAX % $umax) - 1;
            BigInteger::of(self::$UINT32_MAX)
                ->minus(BigInteger::of(self::$UINT32_MAX)->mod($umax))
                ->minus(1);

        $count = 0;

        while ($result->compareTo($limit) > 0) {
            if (++$count > self::RANDOM_RANGE_ATTEMPTS) {
                throw new BrokenRandomEngineError('Failed to generate an acceptable random number in 50 attempts');
            }

            $result = '';
            do {
                $result .= $this->generate();
            } while (\strlen($result) < self::SIZEOF_UINT32_T);

            $result = $this->fromBinary(4, $result);
        }

        return $result->mod($umax);
    }

    /**
     * @param BigInteger $umax
     * @return BigInteger
     */
    private function range64($umax)
    {
        $result = '';
        do {
            $result .= $this->generate();
        } while (\strlen($result) < self::SIZEOF_UINT64_T);

        $result = $this->fromBinary(8, $result);

        if ($umax->isEqualTo(self::$UINT64_MAX)) {
            return $result;
        }

        $umax1 = $umax;
        $umax = BigInteger::of($umax)->plus(1);

        if ($umax->and_($umax1)->isZero()) {
            return $result & $umax1;
        }

        $limit = //self::$UINT64_MAX - (self::$UINT64_MAX % $umax) - 1;
            BigInteger::of(self::$UINT64_MAX)
                ->minus(BigInteger::of(self::$UINT64_MAX)->mod($umax))
                ->minus(1);

        $count = 0;

        while ($result->compareTo($limit) > 0) {
            if (++$count > self::RANDOM_RANGE_ATTEMPTS) {
                throw new BrokenRandomEngineError('Failed to generate an acceptable random number in 50 attempts');
            }

            $result = '';
            do {
                $result .= $this->generate();
            } while (\strlen($result) < self::SIZEOF_UINT64_T);

            $result = $this->fromBinary(4, $result);
        }

        return $result->mod($umax);
    }

    private function rangeBadscaling(/*int */$min, /*int */$max)/*: int*/
    {
        $min = backport_type_check('int', $min);

        $max = backport_type_check('int', $max);

        $n = $this->generate();
        $n = $this->fromBinary(4, $n);
        $n = $n
            ->shiftedRight(1) // self::$math32
            ->toInt(); // self::$math32
        // (__n) = (__min) + (zend_long) ((double) ( (double) (__max) - (__min) + 1.0) * ((__n) / ((__tmax) + 1.0)))
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return \intval($min + \intval((\floatval($max) - $min + 1.0) * ($n / (self::PHP_MT_RAND_MAX + 1.0))));
    }

    public function nextInt()/*: int*/
    {
        $result = $this->generate();
        // @codeCoverageIgnoreStart
        // coverage runs on 64 but this stuff is for 32
        if (\strlen($result) > \PHP_INT_SIZE) {
            throw new RandomException('Generated value exceeds size of int');
        }
        // @codeCoverageIgnoreEnd
        $result = $this->fromBinary(8, $result);

        return $result->shiftedRight(1)->toInt();
    }

    public function getBytes(/*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        if ($length < 1) {
            throw new ValueError(__METHOD__ . '(): Argument #1 ($length) must be greater than 0');
        }

        $retval = '';

        do {
            $retval .= $this->generate();
        } while (\strlen($retval) < $length);

        return \substr($retval, 0, $length);
    }

    public function shuffleArray(array $array)/*: array*/
    {
        // handle empty
        if ($array === []) {
            return [];
        }

        $hash = \array_values($array);
        $nLeft = \count($hash);

        while (--$nLeft) {
            $rndIdx = $this->doGetInt(0, $nLeft);
            $tmp = $hash[$nLeft];
            $hash[$nLeft] = $hash[$rndIdx];
            $hash[$rndIdx] = $tmp;
        }

        return $hash;
    }

    public function shuffleBytes(string $bytes)/*: string*/
    {
        $bytes = backport_type_check('string', $bytes);

        if (\strlen($bytes) <= 1) {
            return $bytes;
        }

        $nLeft = \strlen($bytes);

        while (--$nLeft) {
            $rndIdx = $this->doGetInt(0, $nLeft);
            $tmp = $bytes[$nLeft];
            $bytes[$nLeft] = $bytes[$rndIdx];
            $bytes[$rndIdx] = $tmp;
        }

        return $bytes;
    }

    public function pickArrayKeys(array $array, /*int */$num)/*: array*/
    {
        if (!($this->engine instanceof CryptoSafeEngine)) {
            // Crypto-safe engines are not expected to produce reproducible sequences
            \trigger_error('pickArrayKeys() may produce results incompatible with native ext-random', \E_USER_WARNING);
        }

        if ($array === []) {
            throw new ValueError(__METHOD__ . '(): Argument #1 ($array) cannot be empty');
        }

        $numAvail = \count($array);
        $keys = \array_keys($array);

        if ($num === 1) {
            return [$keys[$this->doGetInt(0, $numAvail - 1)]];
        }

        if ($num <= 0 || $num > $numAvail) {
            throw new ValueError(
                __METHOD__ .
                    '(): Argument #2 ($num) must be between 1 and the number of elements in argument #1 ($array)'
            );
        }

        $retval = [];

        $i = $num;

        while ($i) {
            $idx = $this->doGetInt(0, $numAvail - 1);

            if (\array_key_exists($idx, $retval) === false) {
                $retval[$idx] = $keys[$idx];
                $i--;
            }
        }

        \ksort($retval, \SORT_NUMERIC); // sort by indexes

        return \array_values($retval); // remove indexes
    }

    public function __serialize()/*: array*/
    {
        return [['engine' => $this->engine]];
    }

    public function __unserialize(array $data)/*: void*/
    {
        if (\count($data) !== 1 || !isset($data[0])) {
            throw new Exception(\sprintf('Invalid serialization data for %s object', self::class));
        }

        $this->initMath();

        $fields = $data[0];
        $this->engine = $fields['engine'];
    }

    public function serialize()/*: string*/
    {
        \trigger_error('Serialized object will be incompatible with PHP 8.2', \E_USER_WARNING);
        return \serialize($this->__serialize());
    }

    /**
     * @param string $data
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function unserialize($data)/*: void*/
    {
        $this->__unserialize(\unserialize($data));
    }

    /**
     * @return mixed
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    public function __get(/*string */$name)
    {
        $name = backport_type_check('string', $name);

        if ($name === 'engine') {
            return $this->engine;
        }

        \trigger_error('Undefined property: ' . self::class . '::$' . $name, \E_USER_WARNING);
        return null;
    }

    /**
     * @param mixed $value
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function __set(/*string */$name, $value)/*: void*/
    {
        $name = backport_type_check('string', $name);

        if ($name === 'engine') {
            throw new Error('Cannot modify readonly property Random\Randomizer::$engine');
        }

        $this->nodyn__set($name, $value);
    }

    public function __isset(/*string */$name)/*: bool*/
    {
        $name = backport_type_check('string', $name);

        return $name === 'engine';
    }

    private function toSignedInt($sizeof, $value)
    {
        if ($value & 1 << ($sizeof * 8 - 1)) { // sign
            $value -= 1 << $sizeof * 8;
        }

        return $value;
    }

    public function fromBinary($sizeof, $value)
    {
        switch (backport_spaceship_operator(\strlen($value), $sizeof)) {
            case -1:
                $value = \str_pad($value, $sizeof, "\0");
                break;

            case 1:
                $value = \substr($value, 0, $sizeof);
        }

        return BigInteger::fromBase(\bin2hex(\strrev($value)), 16);
    }
}
