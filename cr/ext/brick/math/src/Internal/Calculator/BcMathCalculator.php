<?php

/*declare(strict_types=1);*/

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
    public function add(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \bcadd($a, $b, 0);
    }

    public function sub(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \bcsub($a, $b, 0);
    }

    public function mul(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \bcmul($a, $b, 0);
    }

    public function divQ(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \bcdiv($a, $b, 0);
    }

    public function divR(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return backport_bcmod($a, $b, 0);
    }

    public function divQR(/*string */$a, /*string */$b)/* : array*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        $q = \bcdiv($a, $b, 0);
        $r = backport_bcmod($a, $b, 0);

        return [$q, $r];
    }

    public function pow(/*string */$a, /*int */$e)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $e = backport_type_check('int', $e);

        return \bcpow($a, (string) $e, 0);
    }

    public function modPow(/*string */$base, /*string */$exp, /*string */$mod)/* : string*/
    {
        $base = backport_type_check('string', $base);

        $exp = backport_type_check('string', $exp);

        $mod = backport_type_check('string', $mod);

        return \bcpowmod($base, $exp, $mod, 0);
    }

    public function sqrt(/*string */$n)/* : string*/
    {
        $n = backport_type_check('string', $n);

        return \bcsqrt($n, 0);
    }
}
