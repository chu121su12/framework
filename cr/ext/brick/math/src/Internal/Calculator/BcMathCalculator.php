<?php

////declare(strict_types=1);

namespace Brick\Math\Internal\Calculator;

use Brick\Math\Internal\Calculator;

/**
 * Calculator implementation built around the bcmath library.
 *
 * @internal
 *
 * @psalm-immutable
 */
class BcMathCalculator extends Calculator
{
    /**
     * {@inheritdoc}
     */
    public function add(/*string */$a, /*string */$b)//// : string
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        return \bcadd($a, $b, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function sub(/*string */$a, /*string */$b)//// : string
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        return \bcsub($a, $b, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function mul(/*string */$a, /*string */$b)//// : string
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        return \bcmul($a, $b, 0);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function divQ(/*string */$a, /*string */$b)//// : string
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        return \bcdiv($a, $b, 0);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function divR(/*string */$a, /*string */$b)//// : string
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        return backport_bcmod($a, $b, 0);
        // if (version_compare(PHP_VERSION, '7.2') >= 0) {
        //     return \bcmod($a, $b, 0);
        // }

        // return \bcmod($a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function divQR(/*string */$a, /*string */$b)//// : array
    {
        $a = cast_to_string($a);

        $b = cast_to_string($b);

        $q = \bcdiv($a, $b, 0);

        $r = backport_bcmod($a, $b, 0);
        // if (version_compare(PHP_VERSION, '7.2') >= 0) {
        //     $r = \bcmod($a, $b, 0);
        // } else {
        //     $r = \bcmod($a, $b);
        // }

        assert($q !== null);
        assert($r !== null);

        return [$q, $r];
    }

    /**
     * {@inheritdoc}
     */
    public function pow(/*string */$a, /*int */$e)//// : string
    {
        $a = cast_to_string($a);

        $e = cast_to_int($e);

        return \bcpow($a, (string) $e, 0);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function modPow(/*string */$base, /*string */$exp, /*string */$mod)//// : string
    {
        $base = cast_to_string($base);

        $exp = cast_to_string($exp);

        $mod = cast_to_string($mod);

        return \bcpowmod($base, $exp, $mod, 0);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public function sqrt(/*string */$n)//// : string
    {
        $n = cast_to_string($n);

        return \bcsqrt($n, 0);
    }
}
